import { createDictionary, setupMongo } from './databaseSetup';
import { DB_COLLECTION_DICTIONARY_ENTRIES, MONGO_DB_NAME } from '../db';
import { connectToDB } from '../mongo';
import { upsertEntries } from '../postEntry';
import { DictionaryEntryItem } from '../entry.model';

setupMongo();

const testUsername = 'test-username';
async function findEntryByGuid(guid: string): Promise<DictionaryEntryItem | null> {
  const dbClient = await connectToDB();
  const db = dbClient.db(MONGO_DB_NAME);
  return db.collection<DictionaryEntryItem>(DB_COLLECTION_DICTIONARY_ENTRIES).findOne({ guid });
}

describe('postEntries', () => {
  test('empty entry created', async () => {
    const dictionaryId = await createDictionary();

    const entryGuid = 'entry-guid';
    await upsertEntries(
      [
        {
          guid: entryGuid,
        },
      ],
      false,
      dictionaryId,
      testUsername,
    );

    const actualEntry = await findEntryByGuid(entryGuid);

    expect(actualEntry).not.toBeNull();
  });

  test('fill mainheadWord from headword', async () => {
    const dictionaryId = await createDictionary();

    const entryGuid = 'headword-entry-guid';
    const headWordValue = 'head-word-value';
    await upsertEntries(
      [
        {
          guid: entryGuid,
          headword: [
            {
              value: headWordValue,
            },
          ],
        },
      ],
      false,
      dictionaryId,
      testUsername,
    );

    const actualEntry = await findEntryByGuid(entryGuid);

    expect(actualEntry?.mainHeadWord.length).toBe(1);
    expect(actualEntry?.mainHeadWord[0].value).toBe(headWordValue);
  });

  test('mainHeadWord exists, ignore headword', async () => {
    const dictionaryId = await createDictionary();

    const entryGuid = 'main-headword-entry-guid';
    const mainHeadWordValue = 'main-head-word-value';
    await upsertEntries(
      [
        {
          guid: entryGuid,
          mainHeadWord: [
            {
              value: mainHeadWordValue,
            },
          ],
          headword: [
            {
              value: 'head-word-value',
            },
          ],
        },
      ],
      false,
      dictionaryId,
      testUsername,
    );

    const actualEntry = await findEntryByGuid(entryGuid);

    expect(actualEntry?.mainHeadWord.length).toBe(1);
    expect(actualEntry?.mainHeadWord[0].value).toBe(mainHeadWordValue);
  });
});
