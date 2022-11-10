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

import { APIGatewayEvent, Context, Callback } from 'aws-lambda';
import { MongoClient, DeleteResult } from 'mongodb';
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
  try {
    // eslint-disable-next-line no-param-reassign
    context.callbackWaitsForEmptyEventLoop = false;

    const dictionaryId = event.pathParameters?.dictionaryId?.toLowerCase();
    const guid = event.queryStringParameters?.guid;

    const isReversalEntry = event.queryStringParameters?.entryType === ENTRY_TYPE_REVERSAL;

    const dbCollection = isReversalEntry
      ? DB_COLLECTION_REVERSAL_ENTRIES
      : DB_COLLECTION_DICTIONARY_ENTRIES;

    if (!guid || guid === '') {
      return callback(null, Response.badRequest('guid must be specified.'));
    }

    dbClient = await connectToDB();
    const db = dbClient.db(MONGO_DB_NAME);

    const count = await db.collection(dbCollection).countDocuments({ guid, dictionaryId });

    if (!count) {
      return callback(null, Response.notFound({}));
    }

    const dbResultEntry: DeleteResult = await db
      .collection(DB_COLLECTION_DICTIONARY_ENTRIES)
      .deleteOne({ guid, dictionaryId });

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
    return callback(null, createFailureResponse(error));
  }
}

export default handler;
