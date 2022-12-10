/**
 * @api {delete} /delete/entry/:dictionaryId Delete entry
 * @apiName DeleteEntry
 * @apiDescription Deletes a dictionary main or reversal entry.
 * @apiGroup Dictionary
 * @apiPermission dictionary admin in Webonary
 * @apiUse BasicAuthHeader
 * @apiUse DictionaryIdPath
 * @apiParam {GUID} guid Id of the entry.
 * @apiParam {String=entry,reversalindexentry} [entryType] Type of the entry to get: 'entry' for main entry and
 * 'reversalindexentry' for reversal entry. Defaults to 'entry'.
 *
 * @apiSuccess {Number} deletedEntryCount The number of main entries deleted.
 *
 * @apiError (404) NotFound Cannot find the specified dictionary.
 */

import { APIGatewayEvent, APIGatewayProxyResult } from 'aws-lambda';
import { MongoClient, DeleteResult } from 'mongodb';
import { connectToDB } from './mongo';
import {
  MONGO_DB_NAME,
  DB_COLLECTION_DICTIONARY_ENTRIES,
  DB_COLLECTION_REVERSAL_ENTRIES,
} from './db';
import { ENTRY_TYPE_REVERSAL } from './entry.model';
import * as Response from './response';

let dbClient: MongoClient;

export async function handler(event: APIGatewayEvent): Promise<APIGatewayProxyResult> {
  const dictionaryId = event.pathParameters?.dictionaryId?.toLowerCase();
  const guid = event.queryStringParameters?.guid;

  const isReversalEntry = event.queryStringParameters?.entryType === ENTRY_TYPE_REVERSAL;

  const dbCollection = isReversalEntry
    ? DB_COLLECTION_REVERSAL_ENTRIES
    : DB_COLLECTION_DICTIONARY_ENTRIES;

  if (!guid || guid === '') {
    return Response.badRequest('guid must be specified.');
  }

  dbClient = await connectToDB();
  const db = dbClient.db(MONGO_DB_NAME);

  const count = await db.collection(dbCollection).countDocuments({ guid, dictionaryId });

  if (!count) {
    return Response.notFound();
  }

  const dbResultEntry: DeleteResult = await db
    .collection(DB_COLLECTION_DICTIONARY_ENTRIES)
    .deleteOne({ guid, dictionaryId });

  // TODO: How to delete S3 files in the dictionary folder???

  return Response.success({
    deletedEntryCount: dbResultEntry.deletedCount,
  });
}

export default handler;
