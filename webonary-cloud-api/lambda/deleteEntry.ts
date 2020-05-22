import { APIGatewayEvent, Context, Callback } from 'aws-lambda';
import { MongoClient, DeleteWriteOpResultObject } from 'mongodb';
import { connectToDB } from './mongo';
import { DB_NAME, DB_COLLECTION_ENTRIES } from './db';
import * as Response from './response';

let dbClient: MongoClient;

export async function handler(
  event: APIGatewayEvent,
  context: Context,
  callback: Callback,
): Promise<void> {
  try {
    // eslint-disable-next-line no-param-reassign
    context.callbackWaitsForEmptyEventLoop = false;

    // const dictionaryId = event.pathParameters?.dictionaryId;
    const _id = event.queryStringParameters?.guid;

    if (!_id || _id === '') {
      return callback(null, Response.badRequest('guid to delete must be specified.'));
    }

    dbClient = await connectToDB();
    const db = dbClient.db(DB_NAME);

    const count = await db.collection(DB_COLLECTION_ENTRIES).countDocuments({ _id });

    if (!count) {
      return callback(null, Response.notFound({}));
    }

    const dbResultEntry: DeleteWriteOpResultObject = await db
      .collection(DB_COLLECTION_ENTRIES)
      .deleteOne({ _id });

    // TODO: How to delete S3 files in the dictionary folder???

    return callback(
      null,
      Response.success({
        deletedEntryCount: dbResultEntry.deletedCount,
      }),
    );
  } catch (error) {
    // eslint-disable-next-line no-console
    console.log(error);
    return callback(null, Response.failure({ errorType: error.name, errorMessage: error.message }));
  }
}

export default handler;
