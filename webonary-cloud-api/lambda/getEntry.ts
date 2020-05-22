import { APIGatewayEvent, Context, Callback } from 'aws-lambda';
import { MongoClient } from 'mongodb';
import { connectToDB } from './mongo';
import { DB_NAME, DB_COLLECTION_ENTRIES } from './db';
import * as Response from './response';

let dbClient: MongoClient;

export async function handler(
  event: APIGatewayEvent,
  context: Context,
  callback: Callback,
): Promise<void> {
  // eslint-disable-next-line no-param-reassign
  context.callbackWaitsForEmptyEventLoop = false;

  const dictionaryId = event.pathParameters?.dictionaryId;
  const _id = event.queryStringParameters?.guid;

  if (!_id || _id === '') {
    return callback(null, Response.badRequest('guid for the dictionary entry must be specified.'));
  }

  try {
    dbClient = await connectToDB();
    const db = dbClient.db(DB_NAME);
    const dbItem = await db.collection(DB_COLLECTION_ENTRIES).findOne({ _id, dictionaryId });
    if (!dbItem) {
      return callback(null, Response.notFound({}));
    }
    return callback(null, Response.success(dbItem));
  } catch (error) {
    // eslint-disable-next-line no-console
    console.log(error);
    return callback(null, Response.failure({ errorType: error.name, errorMessage: error.message }));
  }
}

export default handler;
