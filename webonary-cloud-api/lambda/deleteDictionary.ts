/**
 * @api {delete} /delete/dictionary/:dictionaryId Delete dictionary
 * @apiName DeleteDictionary
 * @apiDescription Deletes a dictionary and all its associated entries, returning the count of everything deleted.
 * @apiGroup Dictionary
 * @apiPermission dictionary admin in Webonary
 * @apiUse BasicAuthHeader
 * @apiUse DictionaryIdPath
 *
 * @apiSuccess {Number} deleteDictionaryCount The number of dictionaries deleted.
 * @apiSuccess {Number} deletedEntryCount The number of main entries deleted.
 * @apiSuccess {Number} deletedReversalCount The number of reversal entries deleted.
 *
 * @apiUse BadRequest
 */

import { APIGatewayEvent, APIGatewayProxyResult } from 'aws-lambda';
import { MongoClient } from 'mongodb';
import { connectToDB } from './mongo';
import {
  MONGO_DB_NAME,
  DB_COLLECTION_DICTIONARIES,
  DB_COLLECTION_DICTIONARY_ENTRIES,
  DB_COLLECTION_REVERSAL_ENTRIES,
} from './db';
import * as Response from './response';

let dbClient: MongoClient;

const dictionaryBucket = process.env.S3_DOMAIN_NAME ?? '';
if (dictionaryBucket === '') {
  throw Error('S3 bucket not set');
}

export async function handler(event: APIGatewayEvent): Promise<APIGatewayProxyResult> {
  const dictionaryId = event.pathParameters?.dictionaryId?.toLowerCase();
  if (!dictionaryId) {
    return Response.badRequest('Invalid parameters');
  }

  dbClient = await connectToDB();
  const db = dbClient.db(MONGO_DB_NAME);

  // eslint-disable-next-line no-console
  console.log(`Start deleting dictionary ${dictionaryId}`);

  const [dbResultDictionary, dbResultEntry, dbResultReversal] = await Promise.all([
    db.collection(DB_COLLECTION_DICTIONARIES).deleteOne({ _id: dictionaryId }),
    db.collection(DB_COLLECTION_DICTIONARY_ENTRIES).deleteMany({ dictionaryId }),
    db.collection(DB_COLLECTION_REVERSAL_ENTRIES).deleteMany({ dictionaryId }),
  ]);

  /*
   * NOTE: deleteDictionaryFolder can take longer than API Gateway max timeout, which is 30 seconds.
   * This will result in 504 Gateway timeout, with message "Endpoint request timed out".
   * There is no way to capture that in a Lambda function, which has a 120 second timeout.
   * So we will not delete files, except via clean up script (TODO).
   */

  // const deletedFilesCount = await deleteS3Folder(dictionaryBucket, dictionaryId);

  const result = {
    deleteDictionaryCount: dbResultDictionary.deletedCount,
    deletedEntryCount: dbResultEntry.deletedCount,
    deletedReversalCount: dbResultReversal.deletedCount,
  };

  // eslint-disable-next-line no-console
  console.log(`Completed deleting dictionary ${dictionaryId}`);
  return Response.success(result);
}

export default handler;
