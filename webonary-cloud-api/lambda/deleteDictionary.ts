import { APIGatewayEvent, Context, Callback } from 'aws-lambda';
import { MongoClient, DeleteWriteOpResultObject } from 'mongodb';
import { connectToDB } from './mongo';
import { DB_NAME, DB_COLLECTION_DICTIONARIES, DB_COLLECTION_ENTRIES } from './db';
import { deleteS3Folder } from './s3Utils';
import * as Response from './response';

let dbClient: MongoClient;

const dictionaryBucket = process.env.S3_DOMAIN_NAME ?? '';
if (dictionaryBucket === '') {
  throw Error('S3 bucket not set');
}

export async function handler(
  event: APIGatewayEvent,
  context: Context,
  callback: Callback,
): Promise<void> {
  try {
    // eslint-disable-next-line no-param-reassign
    context.callbackWaitsForEmptyEventLoop = false;

    const dictionaryId = event.pathParameters?.dictionaryId ?? '';

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

    /*
     * NOTE: deleteDictionaryFolder can take longer than API Gateway max timeout, which is 30 seconds.
     * This will result in 504 Gateway timeout, with message "Endpoint request timed out".
     * There is no way to capture that in a Lambda function, which has a 120 second timeout.
     * But this should be plenty of time to delete all files for a dictionary.
     */
    const deletedFilesCount = await deleteS3Folder(dictionaryBucket, dictionaryId);

    return callback(
      null,
      Response.success({
        deleteDictionaryCount: dbResultDictionary.deletedCount,
        deletedEntryCount: dbResultEntry.deletedCount,
        deletedFilesCount,
      }),
    );
  } catch (error) {
    // eslint-disable-next-line no-console
    console.log(error);
    return callback(null, Response.failure({ errorType: error.name, errorMessage: error.message }));
  }
}

export default handler;
