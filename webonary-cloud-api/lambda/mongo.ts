import { MongoClient } from 'mongodb';

let cachedDb: MongoClient;

export async function connectToDB(): Promise<MongoClient> {
  if (cachedDb) {
    return Promise.resolve(cachedDb);
  }

  const client = await MongoClient.connect(process.env.MONGO_DB_URI as string);

  cachedDb = client;

  return client;
}

export default connectToDB;
