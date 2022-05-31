// It's there in devDependencies. Not sure why it's not working.
// eslint-disable-next-line import/no-extraneous-dependencies
import { MongoMemoryServer } from 'mongodb-memory-server';
import { searchEntries, SearchEntriesArguments } from '../searchEntries';
import { upsertDictionary } from '../postDictionary';

import { upsertEntries } from '../postEntry';

let mongoServer: MongoMemoryServer;

beforeAll(async () => {
  mongoServer = await MongoMemoryServer.create();
  process.env.DB_URL = mongoServer.getUri();
});

afterAll(async () => {
  await mongoServer.stop();
});

let nextDictionaryNumber = 1;
/** Inserts a new dictionary into the database and returns its dictionaryId. */
async function createDictionary(): Promise<string> {
  const dictionaryId = `test-dictionary-${nextDictionaryNumber}`;
  nextDictionaryNumber += 1;

  await upsertDictionary('{}', dictionaryId, 'test-user');

  return dictionaryId;
}

const defaultArguments: SearchEntriesArguments = {
  $language: '',
  countTotalOnly: undefined,
  dictionaryId: 'test-dictionary-default',
  lang: undefined,
  mainLang: undefined,
  matchAccents: undefined,
  matchPartial: undefined,
  pageLimit: 10,
  pageNumber: 1,
  partOfSpeech: undefined,
  searchSemDoms: undefined,
  semDomAbbrev: undefined,
  text: 'test-text',
};

describe('searchEntries', () => {
  test('empty dictionary returns 404', async () => {
    const dictionaryId = await createDictionary();

    const searchResponse = await searchEntries({
      ...defaultArguments,
      dictionaryId,
    });

    expect(searchResponse.statusCode).toBe(404);
  });

  test('mainHeadWord exact match', async () => {
    const dictionaryId = await createDictionary();
    const searchText = 'test-mainHeadWord';
    const matchingGuid = 'test-matching-guid';
    await upsertEntries(
      [
        {
          guid: matchingGuid,
          mainHeadWord: [{ value: searchText }],
        },
        {
          guid: 'test-not-matching-guid',
          mainHeadWord: [{ value: 'something-else-entirely' }],
        },
      ],
      false,
      dictionaryId,
      'test-username',
    );

    const searchResponse = await searchEntries({
      ...defaultArguments,
      dictionaryId,
      text: searchText,
    });

    expect(searchResponse.statusCode).toBe(200);
    const responseBody = JSON.parse(searchResponse.body);
    expect(responseBody.length).toBe(1);
    expect(responseBody[0].dictionaryId).toBe(dictionaryId);
    expect(responseBody[0].guid).toBe(matchingGuid);
  });
});
