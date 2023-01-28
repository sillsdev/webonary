import { createDictionary, setupMongo } from './databaseSetup';
import {
  DB_COLLECTION_REVERSALS,
  MONGO_DB_NAME,
  dbCollectionEntries,
  reversalEntryId,
} from '../db';
import { connectToDB } from '../mongo';
import { upsertEntries } from '../postEntry';

setupMongo();

async function findEntryByGuid(guid: string, dictionaryId: string) {
  const dbClient = await connectToDB();
  return dbClient
    .db(MONGO_DB_NAME)
    .collection(dbCollectionEntries(dictionaryId))
    .findOne({ _id: guid });
}

async function findReversalEntryByGuid(guid: string, dictionaryId: string) {
  const dbClient = await connectToDB();
  return dbClient
    .db(MONGO_DB_NAME)
    .collection(DB_COLLECTION_REVERSALS)
    .findOne({ _id: reversalEntryId({ guid, dictionaryId }) });
}

const guid = 'test-uid';
const testUsername = 'test-username';

describe('postEntries', () => {
  test('empty reversal entry created', async () => {
    const dictionaryId = await createDictionary();
    const isReversal = true;
    await upsertEntries([{ guid }], isReversal, dictionaryId, testUsername);
    const result = await findReversalEntryByGuid(guid, dictionaryId);

    expect(result?.guid).toBe(guid);
    expect(result?.dictionaryId).toBe(dictionaryId);
    expect(result?.updatedAt instanceof Date).toBe(true);
    expect(result?.updatedBy).toBe(testUsername);
  });

  test('empty entry created', async () => {
    const dictionaryId = await createDictionary();
    const isReversal = false;
    await upsertEntries([{ guid }], isReversal, dictionaryId, testUsername);
    const result = await findEntryByGuid(guid, dictionaryId);

    expect(result?.guid).toBe(guid);
    expect(result?.updatedAt instanceof Date).toBe(true);
    expect(result?.updatedBy).toBe(testUsername);
  });

  test('search texts populated from displayXhtml', async () => {
    const dictionaryId = await createDictionary();
    const lang = 'testLang';
    const searchText = 'testSearchText';
    const displayXhtml = `<span lang="${lang}">${searchText}</span>`;
    const isReversal = false;
    await upsertEntries([{ guid, displayXhtml }], isReversal, dictionaryId, testUsername);
    const result = await findEntryByGuid(guid, dictionaryId);

    expect(result?.langTexts).toEqual({ [lang]: [searchText] });
    expect(result?.langUnaccentedTexts).toEqual(result?.langTexts);
    expect(result?.searchTexts).toEqual([searchText]);
  });

  test('search texts ignores duplicates from displayXhtml', async () => {
    const dictionaryId = await createDictionary();
    const lang = 'testLang';
    const lang2 = 'testLang2';
    const searchText = 'testSearchText';
    const displayXhtml = `<span lang="${lang}"><a>${searchText}</a></span><span lang="${lang2}">${searchText}</span>`;
    const isReversal = false;
    await upsertEntries([{ guid, displayXhtml }], isReversal, dictionaryId, testUsername);
    const result = await findEntryByGuid(guid, dictionaryId);

    expect(result?.langTexts).toEqual({ [lang]: [searchText], [lang2]: [searchText] });
    expect(result?.langUnaccentedTexts).toEqual(result?.langTexts);
    expect(result?.searchTexts).toEqual([searchText]);
  });

  test('search texts ignores some classes from displayXhtml', async () => {
    const dictionaryId = await createDictionary();
    const lang = 'testLang';
    const lang2 = 'testLang2';
    const searchText = 'testSearchText';
    const classToIgnore = 'partofspeech';
    const displayXhtml = `<div class="${classToIgnore}"><span lang="${lang}">searchTextToIgnore</a></span></div><div class="${classToIgnore}Not"><span lang="${lang2}">${searchText}</span></div>`;
    const isReversal = false;
    await upsertEntries([{ guid, displayXhtml }], isReversal, dictionaryId, testUsername);
    const result = await findEntryByGuid(guid, dictionaryId);

    expect(result?.langTexts).toEqual({ [lang2]: [searchText] });
    expect(result?.langUnaccentedTexts).toEqual(result?.langTexts);
    expect(result?.searchTexts).toEqual([searchText]);
  });

  test('accented texts populated from displayXhtml', async () => {
    const dictionaryId = await createDictionary();
    const lang = 'testLang';
    const searchText = 'Êêeiîiçc';
    const displayXhtml = `<span lang="${lang}"><a>${searchText}</a></span>`;
    const isReversal = false;
    await upsertEntries([{ guid, displayXhtml }], isReversal, dictionaryId, testUsername);
    const result = await findEntryByGuid(guid, dictionaryId);

    expect(result?.langTexts).toEqual({ [lang]: [searchText] });
    expect(result?.langUnaccentedTexts).toEqual({ [lang]: ['Eeeiiicc'] });
    expect(result?.searchTexts).toEqual([searchText]);
  });
});
