import { searchEntries, SearchEntriesArguments } from '../searchEntries';

import { upsertEntries } from '../postEntry';
import { createDictionary, setupMongo } from './databaseSetup';

setupMongo();

const defaultArguments: SearchEntriesArguments = {
  $language: '',
  countTotalOnly: false,
  dictionaryId: 'test-dictionary-default',
  lang: undefined,
  mainLang: undefined,
  matchAccents: false,
  matchPartial: false,
  pageLimit: 10,
  pageNumber: 1,
  partOfSpeech: undefined,
  searchSemDoms: false,
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

  // TODO: Write a test for languages. I'm not sure what it means to filter by a language. The WordPress implementation
  // seems to assign each entry at most one language, but each entry in the Mongo implementation can have many langs.

  test('partOfSpeech filters out the irrelevant entries', async () => {
    const dictionaryId = await createDictionary();
    await upsertEntries(
        [
          {
            guid: 'guidA',
            displayXhtml: `text`,
            morphoSyntaxAnalysis: {
              partOfSpeech: [
                {value: 'partA'},
              ]
            }
          },
          {
            guid: 'guidAB',
            displayXhtml: `text`,
            morphoSyntaxAnalysis: {
              partOfSpeech: [
                {value: 'partA'},
                {value: 'partB'},
              ]
            }
          },
          {
            guid: 'guidC',
            displayXhtml: `text`,
            morphoSyntaxAnalysis: {
              partOfSpeech: [
                {value: 'partC'},
              ]
            }
          },
          {
            guid: 'guidCD',
            displayXhtml: `text`,
            morphoSyntaxAnalysis: {
              partOfSpeech: [
                {value: 'partC'},
                {value: 'partD'},
              ]
            }
          },
        ],
        false,
        dictionaryId,
        testUsername,
    );

    const searchResponse = await searchEntries({
      ...defaultArguments,
      dictionaryId,
      text: 'text',
      partOfSpeech: 'partA',
    });

    const actualGuids = JSON.parse(searchResponse.body)
        .map((entry: any) => entry.guid)
        .sort();
    expect(actualGuids).toEqual(['guidA', 'guidAB']);
  });

  test('matchPartial match parts of words', async () => {
    const dictionaryId = await createDictionary();
    await upsertEntries(
        [
          {
            guid: 'word',
            displayXhtml: `word`,
          },
          {
            guid: 'or',
            displayXhtml: `or`,
          },
        ],
        false,
        dictionaryId,
        testUsername,
    );

    const searchResponse = await searchEntries({
      ...defaultArguments,
      dictionaryId,
      text: 'or',
      matchPartial: true
    });

    const responseBody = JSON.parse(searchResponse.body);
    expect(responseBody.length).toBe(2);
  });

  test('ignore accents and tones with clean search matches all accents and tones', async () => {
    const dictionaryId = await createDictionary();
    await upsertEntries(
        [
          {
            guid: 'accent',
            displayXhtml: `wórd`,
          },
          {
            guid: 'no-accent',
            displayXhtml: `word`,
          },
        ],
        false,
        dictionaryId,
        testUsername,
    );

    const searchResponse = await searchEntries({
      ...defaultArguments,
      dictionaryId,
      text: 'word',
      matchAccents: false
    });

    const responseBody = JSON.parse(searchResponse.body);
    expect(responseBody.length).toBe(2);
  });

  test('ignore accents and tones with accented search matches all accents and tones', async () => {
    const dictionaryId = await createDictionary();
    await upsertEntries(
        [
          {
            guid: 'accent',
            displayXhtml: `wórd`,
          },
          {
            guid: 'no-accent',
            displayXhtml: `word`,
          },
        ],
        false,
        dictionaryId,
        testUsername,
    );

    const searchResponse = await searchEntries({
      ...defaultArguments,
      dictionaryId,
      text: 'wórd',
      matchAccents: false
    });

    const responseBody = JSON.parse(searchResponse.body);
    expect(responseBody.length).toBe(2);
  });

  test('match accents and tones filters out accents and tones', async () => {
    const dictionaryId = await createDictionary();
    await upsertEntries(
        [
          {
            guid: 'accent',
            displayXhtml: `wórd`,
          },
          {
            guid: 'no-accent',
            displayXhtml: `word`,
          },
        ],
        false,
        dictionaryId,
        testUsername,
    );

    const searchResponse = await searchEntries({
      ...defaultArguments,
      dictionaryId,
      text: 'word',
      matchAccents: true
    });

    const responseBody = JSON.parse(searchResponse.body);
    expect(responseBody.length).toBe(1);
    expect(responseBody[0].guid).toBe('no-accent');
  });

  test('match accents and tones matches only the searched accents and tones', async () => {
    const dictionaryId = await createDictionary();
    await upsertEntries(
        [
          {
            guid: 'accent-1',
            displayXhtml: `wórd`,
          },
          {
            guid: 'accent-2',
            displayXhtml: `wṓrd`,
          },
          {
            guid: 'no-accent',
            displayXhtml: `word`,
          },
        ],
        false,
        dictionaryId,
        testUsername,
    );

    const searchResponse = await searchEntries({
      ...defaultArguments,
      dictionaryId,
      text: 'wṓrd',
      matchAccents: true
    });

    const responseBody = JSON.parse(searchResponse.body);
    expect(responseBody.length).toBe(1);
    expect(responseBody[0].guid).toBe('accent-2');
  });
});
