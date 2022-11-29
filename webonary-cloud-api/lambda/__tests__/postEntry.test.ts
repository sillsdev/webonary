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
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  let testPost: any;
  let dictionaryId: string;

  beforeAll(async () => {
    dictionaryId = await createDictionary();
  });

  beforeEach(() => {
    testPost = {
      guid: 'testGuid',
      mainheadword: [
        {
          lang: 'testLang',
          value: 'testMainHeadWord',
        },
      ],
      citationform: [
        {
          lang: 'testLang',
          value: 'testCitationForm',
        },
        {
          lang: 'testLang2',
          value: 'testCitationForm2',
        },
      ],
      lexemeform: [
        {
          lang: 'testLang2',
          value: 'testLexemeForm2',
        },
      ],
      headword: [
        {
          lang: 'testLang',
          value: 'testHeadWord',
        },
      ],
      senses: [
        {
          definitionOrGloss: [
            {
              lang: 'testLang',
              value: 'definitionOrGlossValue',
            },
            {
              lang: 'testLang2',
              value: 'definitionOrGlossValue2',
            },
          ],
          definition: [
            {
              lang: 'testLang',
              value: 'definitionValue',
            },
          ],
          gloss: [
            {
              lang: 'testLang2',
              value: 'glossValue',
            },
          ],
        },
      ],
    };
  });

  test('empty entry created', async () => {
    const entryGuid = 'empty-guid';
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

  test('mainHeadWord exists, ignore other alternate forms', async () => {
    testPost.guid = 'guid-with-mainheadword';
    await upsertEntries([testPost], false, dictionaryId, testUsername);

    const actualEntry = await findEntryByGuid(testPost.guid);
    expect(actualEntry?.mainHeadWord).toEqual(testPost.mainheadword);
  });

  test('fill mainheadWord from citationform', async () => {
    testPost.guid = 'guid-use-citationform';
    delete testPost.mainheadword;

    await upsertEntries([testPost], false, dictionaryId, testUsername);

    const actualEntry = await findEntryByGuid(testPost.guid);
    expect(actualEntry?.mainHeadWord).toEqual(testPost.citationform);
  });

  test('fill mainheadWord from lexemeform', async () => {
    testPost.guid = 'guid-use-lexemeform';
    delete testPost.mainheadword;
    delete testPost.citationform;

    await upsertEntries([testPost], false, dictionaryId, testUsername);

    const actualEntry = await findEntryByGuid(testPost.guid);
    expect(actualEntry?.mainHeadWord).toEqual(testPost.lexemeform);
  });

  test('fill mainheadWord from headword', async () => {
    testPost.guid = 'guid-use-headword';
    delete testPost.mainheadword;
    delete testPost.citationform;
    delete testPost.lexemeform;

    await upsertEntries([testPost], false, dictionaryId, testUsername);

    const actualEntry = await findEntryByGuid(testPost.guid);
    expect(actualEntry?.mainHeadWord).toEqual(testPost.headword);
  });

  test('dictionaryOrGloss exists, ignore other alternate forms', async () => {
    testPost.guid = 'guid-with-dictionaryOrGloss';
    await upsertEntries([testPost], false, dictionaryId, testUsername);

    const actualEntry = await findEntryByGuid(testPost.guid);
    delete testPost.senses[0].definition;
    delete testPost.senses[0].gloss;

    expect(actualEntry?.senses).toEqual(testPost.senses);
  });

  test('fill dictionaryOrGloss from definition', async () => {
    testPost.guid = 'guid-with-definition';
    delete testPost.senses[0].definitionOrGloss;
    await upsertEntries([testPost], false, dictionaryId, testUsername);

    const actualEntry = await findEntryByGuid(testPost.guid);
    expect(actualEntry?.senses[0].definitionOrGloss).toEqual(testPost.senses[0].definition);
  });

  test('fill dictionaryOrGloss from gloss', async () => {
    testPost.guid = 'guid-with-gloss';
    delete testPost.senses[0].definitionOrGloss;
    delete testPost.senses[0].definition;
    await upsertEntries([testPost], false, dictionaryId, testUsername);

    const actualEntry = await findEntryByGuid(testPost.guid);
    expect(actualEntry?.senses[0].definitionOrGloss).toEqual(testPost.senses[0].gloss);
  });
});
