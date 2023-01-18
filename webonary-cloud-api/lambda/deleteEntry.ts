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
import { MongoClient } from 'mongodb';
import { connectToDB } from './mongo';
import { MONGO_DB_NAME, DB_COLLECTION_REVERSALS, dbCollectionEntries } from './db';
import { ENTRY_TYPE_REVERSAL } from './entry.model';
import * as Response from './response';

let dbClient: MongoClient;

export async function handler(event: APIGatewayEvent): Promise<APIGatewayProxyResult> {
  const dictionaryId = event.pathParameters?.dictionaryId?.toLowerCase();
  if (!dictionaryId) {
    return Response.badRequest('Dictionary must be in the path.');
  }

  const guid = event.queryStringParameters?.guid;
  if (!guid) {
    return Response.badRequest('guid must be specified.');
  }

  let dbCollection;
  let dbFind;
  if (event.queryStringParameters?.entryType === ENTRY_TYPE_REVERSAL) {
    dbCollection = DB_COLLECTION_REVERSALS;
    dbFind = { dictionaryId, guid };
  } else {
    dbCollection = dbCollectionEntries(dictionaryId);
    dbFind = { _id: guid };
  }

  dbClient = await connectToDB();
  const db = dbClient.db(MONGO_DB_NAME);
  const dbResultEntry = await db.collection(dbCollection).deleteOne(dbFind);

  if (!dbResultEntry.deletedCount) {
    return Response.notFound();
  }

  return Response.success({
    deletedEntryCount: dbResultEntry.deletedCount,
  });
}

export default handler;
