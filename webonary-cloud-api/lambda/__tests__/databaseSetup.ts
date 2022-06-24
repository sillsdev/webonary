// 'mongodb-memory-server' is' there in devDependencies. Not sure why it's not working.
// eslint-disable-next-line import/no-extraneous-dependencies
import { MongoMemoryServer } from 'mongodb-memory-server';
import { upsertDictionary } from '../postDictionary';

export function setupMongo(): void {
  let mongoServer: MongoMemoryServer;

  beforeAll(async () => {
    mongoServer = await MongoMemoryServer.create();
    process.env.DB_URL = mongoServer.getUri();
  });

  afterAll(async () => {
    await mongoServer.stop();
  });
}

let nextDictionaryNumber = 1;
/** Inserts a new dictionary into the database and returns its dictionaryId. */
export async function createDictionary(): Promise<string> {
  const dictionaryId = `test-dictionary-${nextDictionaryNumber}`;
  nextDictionaryNumber += 1;

  await upsertDictionary('{}', dictionaryId, 'test-user');

  return dictionaryId;
}
