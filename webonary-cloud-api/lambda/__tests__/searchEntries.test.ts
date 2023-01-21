import { APIGatewayEvent, APIGatewayProxyResult } from 'aws-lambda';

import { handler } from '../searchEntries';
import { upsertEntries } from '../postEntry';
import { createDictionary, setupMongo } from './databaseSetup';
import { removeDiacritics } from '../utils';

setupMongo();

const testUsername = 'test-username';
const text = 'têstFullTextWôrd';

function parseGuids(response: APIGatewayProxyResult): string[] {
  return (
    JSON.parse(response.body)
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      .map((entry: any) => entry.guid)
      .filter((guid: string) => guid)
  );
}

describe('searchEntries params', () => {
  test('empty dictionary returns 404', async () => {
    const dictionaryId = await createDictionary();

    const event: Partial<APIGatewayEvent> = {
      pathParameters: { dictionaryId },
      queryStringParameters: { text },
    };

    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(404);
  });
});

describe('searchEntries search', () => {
  const partialText = text.substring(1);
  const lang = 'testLang';
  const matchingGuid = 'test-matching-guid';
  const partOfSpeech = 'testPartOfSpeech';
  const semanticDomain = '3.4';
  const testEntry = {
    guid: matchingGuid,
    morphosyntaxanalysis: { partofspeech: [{ lang, value: partOfSpeech }] },
    senses: {
      semanticdomains: [{ abbreviation: [{ value: semanticDomain }] }],
    },
    displayXhtml: `<span lang="${lang}">${text}</span>`,
  };

  let dictionaryId = '';
  let event: Partial<APIGatewayEvent> = {};

  beforeEach(async () => {
    dictionaryId = await createDictionary();
    await upsertEntries([testEntry], false, dictionaryId, testUsername);

    // This is the base event, which all tests can start with.
    // By itself, it will do a full text search in the dictionary
    event = {
      pathParameters: { dictionaryId },
      queryStringParameters: { text },
    };
  });

  test('matches word in displayXhtml', async () => {
    // Use base event without modification
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([matchingGuid]);
  });

  test('does not match word with wrong lang', async () => {
    event = {
      ...event,
      queryStringParameters: { ...event.queryStringParameters, lang: `${lang}Not` },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(404);
  });

  test('fulltext search matches word with lang', async () => {
    event = {
      ...event,
      queryStringParameters: { lang, text },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([matchingGuid]);
  });

  test('fulltext search does not match partial text with lang', async () => {
    event = {
      ...event,
      queryStringParameters: { lang, text: partialText },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(404);
  });

  test('partial search matches partial text with lang', async () => {
    event = {
      ...event,
      queryStringParameters: { lang, text: partialText, matchPartial: '1' },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([matchingGuid]);
  });

  test('fulltext search matches word case insensitive', async () => {
    event = {
      ...event,
      queryStringParameters: { lang, text: text.toUpperCase() },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([matchingGuid]);
  });

  test('partial search matches partial text case insensitive', async () => {
    event = {
      ...event,
      queryStringParameters: { lang, text: partialText.toUpperCase(), matchPartial: '1' },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([matchingGuid]);
  });

  test('fulltext search matches word accent sensitive', async () => {
    event = {
      ...event,
      queryStringParameters: { lang, text, matchAccents: '1' },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([matchingGuid]);
  });

  test('partial search matches partial text accent sensitive', async () => {
    event = {
      ...event,
      queryStringParameters: { lang, text: partialText, matchAccents: '1', matchPartial: '1' },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([matchingGuid]);
  });

  test('fulltext search does not match accent sensitive', async () => {
    event = {
      ...event,
      queryStringParameters: {
        lang,
        text: removeDiacritics(text),
        matchAccents: '1',
      },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(404);
  });

  test('partial search does not match accent sensitive', async () => {
    event = {
      ...event,
      queryStringParameters: {
        lang,
        text: removeDiacritics(partialText),
        matchAccents: '1',
        matchPartial: '1',
      },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(404);
  });

  test('matches part of speech', async () => {
    event = {
      ...event,
      queryStringParameters: { lang, text },
      multiValueQueryStringParameters: { partOfSpeech: [partOfSpeech, `${partOfSpeech}Not`] },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([matchingGuid]);
  });

  test('does not match part of speech', async () => {
    event = {
      ...event,
      queryStringParameters: { lang, text },
      multiValueQueryStringParameters: { partOfSpeech: [`${partOfSpeech}Not`] },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(404);
  });

  test('matches semantic domain exactly', async () => {
    event = {
      ...event,
      queryStringParameters: { lang, text, semanticDomain },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([matchingGuid]);
  });

  test('matches semantic domain partially', async () => {
    event = {
      ...event,
      queryStringParameters: { lang, text, semanticDomain: '3' },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([matchingGuid]);
  });

  test('does not match semantic domain', async () => {
    event = {
      ...event,
      queryStringParameters: { lang, text, semanticDomain: `1.${semanticDomain}` },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(404);
  });

  test('matches part of speech and semantic domain', async () => {
    event = {
      ...event,
      queryStringParameters: { lang, text, semanticDomain },
      multiValueQueryStringParameters: { partOfSpeech: [partOfSpeech] },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([matchingGuid]);
  });

  test('matches semantic domain but not part of speech', async () => {
    event = {
      ...event,
      queryStringParameters: { lang, text, semanticDomain },
      multiValueQueryStringParameters: { partOfSpeech: [`${partOfSpeech}Not`] },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(404);
  });

  test('search matches text with lang that has special characters', async () => {
    const specialGuid = `${matchingGuid}-special`;
    const specialCharsText = 'abc.d[e]f(g)h';
    const specialCharsEntry = {
      ...testEntry,
      guid: specialGuid,
      displayXhtml: `<span lang="${lang}">${specialCharsText}</span>`,
    };

    await upsertEntries([specialCharsEntry], false, dictionaryId, testUsername);

    // fulltext search
    event = {
      ...event,
      queryStringParameters: { lang, text: specialCharsText },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([specialGuid]);

    // partial text search
    event = {
      ...event,
      queryStringParameters: { lang, text: specialCharsText.substring(1), matchPartial: '1' },
    };
    const response2 = await handler(event as APIGatewayEvent);
    expect(response2.statusCode).toBe(200);
    expect(parseGuids(response2)).toEqual([specialGuid]);
  });

  test('matches and gives count', async () => {
    event = {
      ...event,
      queryStringParameters: { lang, text, countTotalOnly: '1' },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(JSON.parse(response.body).count).toEqual(1);
  });
});
