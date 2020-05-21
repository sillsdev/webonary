import { APIGatewayEvent, Context, Callback } from 'aws-lambda';
import { MongoClient, DeleteWriteOpResultObject } from 'mongodb';
import { connectToDB } from './mongo';
import { DB_NAME, DB_COLLECTION_DICTIONARIES, DB_COLLECTION_ENTRIES } from './db';
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

    const dictionaryId = event.pathParameters?.dictionaryId;

    dbClient = await connectToDB();
    const db = dbClient.db(DB_NAME);

    const count = await db
      .collection(DB_COLLECTION_DICTIONARIES)
      .countDocuments({ _id: dictionaryId });

    if (!count) {
      return callback(null, Response.notFound({}));
    }

    const dbResultDictionary: DeleteWriteOpResultObject = await db
      .collection(DB_COLLECTION_DICTIONARIES)
      .deleteOne({ _id: dictionaryId });

    const dbResultEntry: DeleteWriteOpResultObject = await db
      .collection(DB_COLLECTION_ENTRIES)
      .deleteMany({ dictionaryId });

    // TODO: How to delete S3 files in the dictionary folder???

    return callback(
      null,
      Response.success({
        deleteDictionaryCount: dbResultDictionary.deletedCount,
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
