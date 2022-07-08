import { Db } from 'mongodb';
import { MigrationInterface } from 'mongo-migrate-ts';

import { DbPaths } from 'lambda/entry.model';
import {
  DB_COLLECTION_DICTIONARY_ENTRIES,
  DB_COLLATION_LOCALE_DEFAULT_FOR_INSENSITIVITY,
  DB_COLLATION_STRENGTH_FOR_CASE_INSENSITIVITY,
} from 'lambda/db';

const entriesFulltextIndexName = 'wordsFulltextIndex';
const entriesHeadwordIndexName = `${DbPaths.ENTRY_MAIN_HEADWORD_LANG}_1_${DbPaths.ENTRY_MAIN_HEADWORD_VALUE}_1`;

export class Migration_2022_07_06T1320Z_CreateIndexes implements MigrationInterface {
  public async up(db: Db): Promise<any> {
    // fulltext index (case and diacritic insensitive by default)
    await db.collection(DB_COLLECTION_DICTIONARY_ENTRIES).dropIndex(entriesFulltextIndexName);
    await db.collection(DB_COLLECTION_DICTIONARY_ENTRIES).createIndex(
      {
        [DbPaths.ENTRY_DISPLAY_TEXT]: 'text',
      },
      {
        name: entriesFulltextIndexName,
        default_language: 'none',
      },
    );

    // case and diacritic insensitive index for semantic domains
    await db.collection(DB_COLLECTION_DICTIONARY_ENTRIES).dropIndex(entriesHeadwordIndexName);
    await db.collection(DB_COLLECTION_DICTIONARY_ENTRIES).createIndex(
      {
        [DbPaths.ENTRY_MAIN_HEADWORD_LANG]: 1,
        [DbPaths.ENTRY_MAIN_HEADWORD_VALUE]: 1,
      },
      {
        name: entriesHeadwordIndexName,
        collation: {
          locale: DB_COLLATION_LOCALE_DEFAULT_FOR_INSENSITIVITY,
          strength: DB_COLLATION_STRENGTH_FOR_CASE_INSENSITIVITY,
        },
      },
    );
  }

  public async down(db: Db): Promise<any> {
    await db.collection(DB_COLLECTION_DICTIONARY_ENTRIES).dropIndex(entriesFulltextIndexName);
    await db.collection(DB_COLLECTION_DICTIONARY_ENTRIES).dropIndex(entriesHeadwordIndexName);
  }
}
