// 'mongodb-memory-server' is' there in devDependencies. Not sure why it's not working.
// eslint-disable-next-line import/no-extraneous-dependencies
import { MongoMemoryServer } from 'mongodb-memory-server';
import { connectToDB } from '../mongo';
import { MONGO_DB_NAME, createIndexes } from '../db';

import { upsertDictionary } from '../postDictionary';

export function setupMongo(): void {
  let mongoServer: MongoMemoryServer;

  beforeAll(async () => {
    mongoServer = await MongoMemoryServer.create();
    process.env.MONGO_DB_URI = mongoServer.getUri();

    const dbClient = await connectToDB();
    const db = dbClient.db(MONGO_DB_NAME);
    await createIndexes(db);
  });

  afterAll(async () => {
    await mongoServer.stop();
  });
}

let nextDictionaryNumber = 1;
/** Inserts a new dictionary into the database and returns its dictionaryId. */
export async function createDictionary(data = '{}'): Promise<string> {
  const dictionaryId = `test-dictionary-${nextDictionaryNumber}`;
  nextDictionaryNumber += 1;

  await upsertDictionary(data, dictionaryId, 'test-user');
  return dictionaryId;
}
