import { MongoClient } from 'mongodb';

let cachedDb: MongoClient;

export const DB_URL = process.env.DB_URL as string;
export const DB_NAME = process.env.DB_NAME as string;
export const COLLECTION_ENTRIES = 'webonaryEntries';
export const DB_MAX_UPDATES_PER_CALL = 50;

export interface DbFindParameters {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  [key: string]: any;
}

export async function connectToDB(): Promise<MongoClient> {
  if (cachedDb) {
    return Promise.resolve(cachedDb);
  }

  const client = await MongoClient.connect(DB_URL as string, {
    useNewUrlParser: true,
  });

  cachedDb = client;

  return client;
}
