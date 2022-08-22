import { Db } from 'mongodb';
import { MigrationInterface } from 'mongo-migrate-ts';

/* eslint-disable-next-line */
import { createIndexes, dropIndexes } from '../../lambda/db'; // use relative path so mongo-migrate cli can find it

export class Migration20220706T1320ZCreateIndexes implements MigrationInterface {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  public async up(db: Db): Promise<any> {
    await dropIndexes(db);
    await createIndexes(db);
  }

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  public async down(db: Db): Promise<any> {
    await dropIndexes(db);
  }
}
