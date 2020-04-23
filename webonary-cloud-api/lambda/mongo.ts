import { MongoClient } from 'mongodb';

let cachedDb: MongoClient;

const DB_URL = process.env.DB_URL as string;

export async function connectToDB(): Promise<MongoClient> {
  if (cachedDb && cachedDb.isConnected()) {
    return Promise.resolve(cachedDb);
  }

  const client = await MongoClient.connect(DB_URL as string, {
    useNewUrlParser: true,
  });

  cachedDb = client;

  return client;
}

export default connectToDB();
