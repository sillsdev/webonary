import { searchEntries, SearchEntriesArguments } from '../searchEntries';

import { upsertEntries } from '../postEntry';
import { createDictionary, setupMongo } from './databaseSetup';

setupMongo();

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

const testUsername = 'test-username';
describe('searchEntries', () => {
  test('empty dictionary returns 404', async () => {
    const dictionaryId = await createDictionary();

    const searchResponse = await searchEntries({
      ...defaultArguments,
      dictionaryId,
    });

    expect(searchResponse.statusCode).toBe(404);
  });

  test('matches text only in displayXhtml', async () => {
    const dictionaryId = await createDictionary();
    const searchText = 'test-mainHeadWord';
    const matchingGuid = 'test-matching-guid';
    await upsertEntries(
      [
        {
          guid: matchingGuid,
          displayXhtml: `<div>${searchText}</div>`,
        },
      ],
      false,
      dictionaryId,
      testUsername,
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

  test('does not match tags in displayXhtml', async () => {
    const dictionaryId = await createDictionary();
    await upsertEntries(
      [
        {
          guid: 'test-guid',
          displayXhtml: `<div>some text</div>`,
        },
      ],
      false,
      dictionaryId,
      testUsername,
    );

    const searchResponse = await searchEntries({
      ...defaultArguments,
      dictionaryId,
      text: 'div',
    });

    expect(searchResponse.statusCode).toBe(404);
  });

  test('does not match tag attributes in displayXhtml', async () => {
    const dictionaryId = await createDictionary();
    await upsertEntries(
      [
        {
          guid: 'test-guid',
          displayXhtml: `<a href="http://localhost">some text</a>`,
        },
      ],
      false,
      dictionaryId,
      testUsername,
    );

    const searchResponse = await searchEntries({
      ...defaultArguments,
      dictionaryId,
      text: 'href',
    });

    expect(searchResponse.statusCode).toBe(404);
  });

  test('does not match tag attribute values in displayXhtml', async () => {
    const dictionaryId = await createDictionary();
    await upsertEntries(
      [
        {
          guid: 'test-guid',
          displayXhtml: `<a href="http://localhost">some text</a>`,
        },
      ],
      false,
      dictionaryId,
      testUsername,
    );

    const searchResponse = await searchEntries({
      ...defaultArguments,
      dictionaryId,
      text: 'localhost',
    });

    expect(searchResponse.statusCode).toBe(404);
  });
});
