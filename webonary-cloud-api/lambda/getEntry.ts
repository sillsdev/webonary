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

import { APIGatewayEvent, Context, Callback } from 'aws-lambda';
import { MongoClient } from 'mongodb';
import { connectToDB } from './mongo';
import {
  MONGO_DB_NAME,
  DB_COLLECTION_DICTIONARY_ENTRIES,
  DB_COLLECTION_REVERSAL_ENTRIES,
} from './db';
import { ENTRY_TYPE_REVERSAL } from './entry.model';
import * as Response from './response';
import { createFailureResponse } from './utils';

let dbClient: MongoClient;

export async function handler(
  event: APIGatewayEvent,
  context: Context,
  callback: Callback,
): Promise<void> {
  // eslint-disable-next-line no-param-reassign
  context.callbackWaitsForEmptyEventLoop = false;

  const dictionaryId = event.pathParameters?.dictionaryId;
  const guid = event.queryStringParameters?.guid;

  const isReversalEntry = event.queryStringParameters?.entryType === ENTRY_TYPE_REVERSAL;

  const dbCollection = isReversalEntry
    ? DB_COLLECTION_REVERSAL_ENTRIES
    : DB_COLLECTION_DICTIONARY_ENTRIES;

  if (!guid || guid === '') {
    return callback(null, Response.badRequest('guid must be specified.'));
  }

  try {
    dbClient = await connectToDB();
    const db = dbClient.db(MONGO_DB_NAME);
    const dbItem = await db.collection(dbCollection).findOne({ guid, dictionaryId });
    if (!dbItem) {
      return callback(null, Response.notFound({}));
    }
    return callback(null, Response.success(dbItem));
  } catch (error) {
    // eslint-disable-next-line no-console
    console.log(error);
    return callback(null, createFailureResponse(error));
  }
}

export default handler;
