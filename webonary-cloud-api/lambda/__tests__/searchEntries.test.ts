import { APIGatewayEvent } from 'aws-lambda';

import { createDictionary, setupMongo } from './databaseSetup';
import { parseGuids } from './utils';

import { upsertEntries } from '../postEntry';
import { handler } from '../searchEntries';
import { removeDiacritics } from '../utils';

setupMongo();

const testUsername = 'test-username';
const text = 'têstFullTextWôrd';

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
    dictionaryId = await createDictionary(
      JSON.stringify({
        mainLanguage: {
          lang: 'testLang',
          title: 'Language with hyphen as a word-forming character',
          letters: [
            '-',
            'a',
            'b',
            'c',
            'd',
            'e',
            'ɛ',
            'ə',
            'f',
            'g',
            'h',
            'i',
            'j',
            'k',
            'l',
            'm',
            'n',
            'o',
            'ɔ',
            'p',
            'r',
            's',
            't',
            'u',
            'v',
            'w',
            'y',
          ],
        },
      }),
    );

    await upsertEntries([testEntry], false, dictionaryId, testUsername);

    // This is the base event, which all tests can start with.
    // By itself, it will do a full text search in the dictionary
    event = {
      pathParameters: { dictionaryId },
      queryStringParameters: { text },
    };
  });

  test('no search params', async () => {
    event = {
      ...event,
      queryStringParameters: { ...event.queryStringParameters, text: '' },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(400);
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

  test('matches part of speech with no search text', async () => {
    event = {
      ...event,
      queryStringParameters: { lang, text: '' },
      multiValueQueryStringParameters: { partOfSpeech: [partOfSpeech, `${partOfSpeech}Not`] },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([matchingGuid]);
  });

  test('matches part of speech using shared gram info', async () => {
    const sharedGramGuid = `${matchingGuid}-part-shared`;
    const sharedGramEntry = {
      ...testEntry,
      guid: sharedGramGuid,
      morphosyntaxanalysis: { partofspeech: [{ lang, value: `${partOfSpeech}sharedGram` }] },
    };

    await upsertEntries([sharedGramEntry], false, dictionaryId, testUsername);

    event = {
      ...event,
      queryStringParameters: { lang, text },
      multiValueQueryStringParameters: {
        partOfSpeech: [`${partOfSpeech}sharedGram`],
      },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([sharedGramGuid]);
  });

  test('matches part of speech using senses', async () => {
    const sensesPartGuid = `${matchingGuid}-senses-part`;
    const sensesPartEntry = {
      ...testEntry,
      guid: sensesPartGuid,
      senses: [
        { morphosyntaxanalysis: { partofspeech: [{ lang, value: `${partOfSpeech}sensesPart` }] } },
      ],
    };

    await upsertEntries([sensesPartEntry], false, dictionaryId, testUsername);

    event = {
      ...event,
      queryStringParameters: { lang, text },
      multiValueQueryStringParameters: {
        partOfSpeech: [`${partOfSpeech}sensesPart`],
      },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([sensesPartGuid]);
  });

  test('matches part of speech using subentries', async () => {
    const subentriesPartGuid = `${matchingGuid}-subentries-part`;
    const subentriesPartEntry = {
      ...testEntry,
      guid: subentriesPartGuid,
      'subentries mainentrysubentries': [
        { morphosyntaxanalysis: { partofspeech: [{ lang, value: `${partOfSpeech}sensesPart` }] } },
      ],
    };

    await upsertEntries([subentriesPartEntry], false, dictionaryId, testUsername);

    event = {
      ...event,
      queryStringParameters: { lang, text },
      multiValueQueryStringParameters: {
        partOfSpeech: [`${partOfSpeech}sensesPart`],
      },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([subentriesPartGuid]);
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

  test('matches part of speech and semantic domain with no search text', async () => {
    event = {
      ...event,
      queryStringParameters: { lang, text: '', semanticDomain },
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

  test('fulltext search matches word with hyphen', async () => {
    // add an entry with a hyphen
    const testGuid = `${matchingGuid}-matches-hyphen`;
    const newTestEntry = {
      ...testEntry,
      guid: testGuid,
      displayXhtml: `<span lang="${lang}">qwerty-asdfgh</span>`,
    };
    await upsertEntries([newTestEntry], false, dictionaryId, testUsername);

    // search for the whole the word, should find the entry
    let newEvent = {
      ...event,
      queryStringParameters: { lang, text: 'qwerty-asdfgh' },
    };
    let response = await handler(newEvent as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([testGuid]);

    // search for the first part of the word, should NOT find the entry
    newEvent = {
      ...event,
      queryStringParameters: { lang, text: 'qwerty' },
    };
    response = await handler(newEvent as APIGatewayEvent);
    expect(response.statusCode).toBe(404);
  });
});
