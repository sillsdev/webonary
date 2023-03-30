import { APIGatewayProxyResult } from 'aws-lambda';
import { MongoMemoryServer } from 'mongodb-memory-server';

import { MONGO_DB_NAME, createReversalsIndexes } from '../db';
import { connectToDB } from '../mongo';
import { upsertDictionary } from '../postDictionary';

export function setupMongo(): void {
  let mongoServer: MongoMemoryServer;

  beforeAll(async () => {
    mongoServer = await MongoMemoryServer.create();
    process.env.MONGO_DB_URI = mongoServer.getUri();

    const dbClient = await connectToDB();
    const db = dbClient.db(MONGO_DB_NAME);
    await createReversalsIndexes(db);
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

export function parseGuids(response: APIGatewayProxyResult): string[] {
  return (
    JSON.parse(response.body)
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      .map((entry: any) => entry.guid)
      .filter((guid: string) => guid)
  );
}
