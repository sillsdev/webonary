import { APIGatewayEvent } from 'aws-lambda';

import { createDictionary, setupMongo } from './databaseSetup';
import { parseGuids } from './utils';

import { handler } from '../browseEntries';
import { ENTRY_TYPE_REVERSAL } from '../entry.model';
import { upsertEntries } from '../postEntry';

setupMongo();

const lang = 'testLang';
const matchingGuid = 'test-matching-guid';
const testUsername = 'test-username';

describe('browse reversal entries', () => {
  let dictionaryId = '';
  let event: Partial<APIGatewayEvent> = {};

  const testEntries = ['a', 'b', 'B', 'ba'].map((letterHead, sortIndex) => {
    return {
      guid: `${matchingGuid}-${letterHead}`,
      letterHead,
      reversalform: [{ lang, value: `value${letterHead}` }],
      sortIndex,
    };
  });

  const letterHead = 'a'; // note this is different lang from above
  testEntries.push({
    guid: `${matchingGuid}-${letterHead}-anotherLang`,
    letterHead,
    reversalform: [{ lang: 'anotherLang', value: `value${letterHead}` }],
    sortIndex: 2,
  });

  beforeEach(async () => {
    dictionaryId = await createDictionary();
    await upsertEntries(testEntries, true, dictionaryId, testUsername);

    // This is the base event, which all tests can start with.
    // By itself, it will do a full text search in the dictionary
    event = {
      pathParameters: { dictionaryId },
      queryStringParameters: { entryType: ENTRY_TYPE_REVERSAL },
    };
  });

  test('require browse letter in text query param', async () => {
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(400);
  });

  test('require lang in text query param', async () => {
    event = {
      ...event,
      queryStringParameters: { ...event.queryStringParameters, text: 'a' },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(400);
  });

  test('match letter head', async () => {
    const text = 'a';
    event = {
      ...event,
      queryStringParameters: { ...event.queryStringParameters, lang, text },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([`${matchingGuid}-${text}`]);
  });

  test('match letter head different case', async () => {
    const text = 'A';
    event = {
      ...event,
      queryStringParameters: { ...event.queryStringParameters, lang, text },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([`${matchingGuid}-${text.toLocaleLowerCase()}`]);
  });

  test('match letter head case insensitive', async () => {
    const text = 'b';
    event = {
      ...event,
      queryStringParameters: { ...event.queryStringParameters, lang, text },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([
      `${matchingGuid}-${text}`,
      `${matchingGuid}-${text.toLocaleUpperCase()}`,
    ]);
  });

  test('matches and returns count', async () => {
    const text = 'b';
    event = {
      ...event,
      queryStringParameters: { ...event.queryStringParameters, lang, text, countTotalOnly: '1' },
    };

    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(JSON.parse(response.body).count).toEqual(2);
  });
});

describe('browse dictionary entries', () => {
  let dictionaryId = '';
  let event: Partial<APIGatewayEvent> = {};

  const testEntries = ['a', 'b', 'B', 'ba'].map((letterHead, sortIndex) => {
    return {
      guid: `${matchingGuid}-${letterHead}`,
      letterHead,
      sortIndex,
    };
  });

  beforeEach(async () => {
    dictionaryId = await createDictionary();
    await upsertEntries(testEntries, false, dictionaryId, testUsername);

    // This is the base event, which all tests can start with.
    // By itself, it will do a full text search in the dictionary
    event = { pathParameters: { dictionaryId } };
  });

  test('require browse letter in text query param', async () => {
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(400);
  });

  test('matches and return in correct sort order', async () => {
    const text = 'b';
    event = {
      ...event,
      queryStringParameters: { ...event.queryStringParameters, text },
    };
    const response = await handler(event as APIGatewayEvent);
    expect(response.statusCode).toBe(200);
    expect(parseGuids(response)).toEqual([
      `${matchingGuid}-${text}`,
      `${matchingGuid}-${text.toLocaleUpperCase()}`,
    ]);
  });
});
