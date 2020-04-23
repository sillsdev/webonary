import { MongoClient } from 'mongodb';

let cachedDb: MongoClient;

export const DB_URL = process.env.DB_URL as string;
export const DB_NAME = process.env.DB_NAME as string;
export const COLLECTION_ENTRIES = 'webonaryEntries';

export interface DbFindParameters {
  [key: string]: any;
}

export async function connectToDB(): Promise<MongoClient> {
  console.log('=> connect to database');

  if (cachedDb) {
    // console.log('=> using cached database instance');
    // return Promise.resolve(cachedDb);
  }

  const client = await MongoClient.connect(DB_URL as string, {
    useNewUrlParser: true,
    // useUnifiedTopology: true,
  });

  cachedDb = client;

  return client;
}

function buildResponse(statusCode: number, body: object): object {
  return {
    statusCode,
    headers: {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Credentials': true,
    },
    body: JSON.stringify(body),
  };
}

export function success(body: object): object {
  return buildResponse(200, body);
}

export function failure(body: object): object {
  return buildResponse(500, body);
}

export function notFound(body: object): object {
  return buildResponse(404, body);
}

export function redirect(location: string): object {
  return {
    statusCode: 302,
    headers: {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Credentials': true,
      Location: location,
    },
  };
}
