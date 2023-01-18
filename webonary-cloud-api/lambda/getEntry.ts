/**
 * @api {get} /get/entry/:dictionaryId Get dictionary or reversal entry
 * @apiName GetDictionaryEntry
 * @apiDescription Gets a single dictionary or reversal entry. Returns a ReversalEntryItem if entryType ==
 * 'reversalindexentry' else a DictionaryEntryItem.
 * (https://github.com/sillsdev/webonary/blob/develop/webonary-cloud-api/lambda/entry.model.ts)
 * @apiGroup Dictionary
 * @apiUse DictionaryIdPath
 * @apiParam {GUID} guid Id of the entry.
 * @apiParam {String=entry,reversalindexentry} [entryType] Type of the entry to get: 'entry' for main entry and
 * 'reversalindexentry' for reversal entry. Defaults to 'entry'.
 *
 * @apiError (404) NotFound Cannot find the specified entry.
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

  // eslint-disable-next-line no-console
  console.log(`Getting entry from ${dbCollection}...`, dbFind);

  dbClient = await connectToDB();
  const db = dbClient.db(MONGO_DB_NAME);
  const dbItem = await db.collection(dbCollection).findOne(dbFind);
  if (!dbItem) {
    return Response.notFound();
  }

  return Response.success(dbItem);
}

export default handler;
