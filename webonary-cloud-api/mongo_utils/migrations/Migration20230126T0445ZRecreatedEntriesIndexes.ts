/* eslint-disable @typescript-eslint/no-explicit-any */
/* eslint-disable import/no-relative-packages */
/* eslint-disable no-await-in-loop */
/* eslint-disable no-console */
import { Db } from 'mongodb';
import { MigrationInterface } from 'mongo-migrate-ts';

// use relative path so mongo-migrate cli can find it
import {
  DB_COLLECTION_DICTIONARIES,
  createEntriesIndexes,
  dropEntriesIndexes,
} from '../../lambda/db';

export class Migration20230126T0445ZRecreatedEntriesIndexes implements MigrationInterface {
  public async up(db: Db): Promise<any> {
    const dictionaries = await db
      .collection(DB_COLLECTION_DICTIONARIES)
      .find({})
      .sort({ _id: 1 })
      .project({ _id: 1 })
      .toArray();

    // sequentially process to not overburden Mongo
    // eslint-disable-next-line no-restricted-syntax
    for (const { _id: dictionaryId } of dictionaries) {
      console.log(`\nDropping indexes for dictionary ${dictionaryId} entries`);
      await dropEntriesIndexes(db, dictionaryId);

      console.log(`\nCreating indexes for dictionary ${dictionaryId} entries`);
      await createEntriesIndexes(db, dictionaryId);
    }
  }

  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  public async down(db: Db): Promise<any> {
    console.log('Nothing to undo here!');
  }
}
