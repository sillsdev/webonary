import { Db } from 'mongodb';
import { MigrationInterface } from 'mongo-migrate-ts';

/* eslint-disable-next-line */
import { createIndexes, dropIndexes } from '../../lambda/db'; // use relative path so the cli can find it

export class Migration20220706T1320ZCreateIndexes implements MigrationInterface {
  public async up(db: Db): Promise<any> {
    await dropIndexes(db);
    await createIndexes(db);
  }

  public async down(db: Db): Promise<any> {
    await dropIndexes(db);
  }
}
