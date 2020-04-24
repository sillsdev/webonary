/**
 * @api {post} /load/entry/:dictionary Load entry
 * @apiName LoadDictionaryEntry
 * @apiGroup Dictionary
 * @apiPermission dictionary admin in Webonary
 *
 * @apiHeader Authorization Basic Auth value corresponding to <a href=https://www.webonary.org>Webonary</a> dictionary site's admin username and password
 * @apiHeader Content-Type application/json
 * @apiHeaderExample {Header} Header-Example
 *    Authorization: Basic YWRtaW46cGFzc3dvcmQ=
 *
 * @apiParam {String}   dictionary  Unique dictionary id registered in <a href=https://www.webonary.org>Webonary</a>
 *
 * @apiParam {Object[]} body       Array of dictionary entries
 * @apiParam {String}   body.guid  GUID of the entry
 * @apiParam {Object}   body.data  Object of entry data
 *
 * @apiParamExample {json} Request-Example:
 *     [
 *       {
 *         "guid": "edea14f7-e59c-494c-b7c1-94e00f5f8a81",
 *         "data": {
 *           "term": "hijo",
 *           "definition": "son"
 *         }
 *       },
 *       {
 *         "guid": "edea14f7-e59c-494c-b7c1-94e00f5f8a81",
 *         "data": {
 *           "term": "hija",
 *           "definition": "daughter"
 *         }
 *       }
 *     ]
 * @apiSuccess {String} updatedAt Timestamp of the loading of entries in GMT
 * @apiSuccess {Number} updatedCount Number of entries updated
 * @apiSuccess {Number} insertedCount Number of entries inserted
 * @apiSuccess {Object[]} insertedGUIDs Array containing GUID of the inserted entries
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "updatedAt": "Thu, 23 Apr 2020 17:00:15 GMT",
 *       "updatedCount": 48,
 *       "insertedCount": 2,
 *       "insertedGUIDs": [
 *         "edea14f7-e59c-494c-b7c1-94e00f5f8a81",
 *         "496e6865-bf0d-40aa-9834-93b47404ed93"
 *       ]
 *     }
 *
 * @apiError InvalidRequest Input should be an array of up to 50 entry objects.
 *
 * @apiErrorExample {json} Error-Response:
 *     HTTP/1.1 400 InvalidRequest
 *     {
 *       "errorType": "InvalidRequest",
 *       "errorMessage": "Input must be an array of entries"
 *     }
 */

import { APIGatewayEvent, Callback, Context } from 'aws-lambda';
import { MongoClient, ObjectId, UpdateWriteOpResult } from 'mongodb';
import { connectToDB } from './mongo';
import { DB_NAME, COLLECTION_ENTRIES, DB_MAX_UPDATES_PER_CALL, LoadEntry } from './db';
import * as Response from './response';

interface LoadResult {
  updatedAt: string;
  updatedCount: number;
  insertedCount: number;
  insertedGUIDs: ObjectId[];
}

let dbClient: MongoClient;

export async function handler(
  event: APIGatewayEvent,
  context: Context,
  callback: Callback,
): Promise<void> {
  // eslint-disable-next-line no-param-reassign
  context.callbackWaitsForEmptyEventLoop = false;

  const entries: LoadEntry[] = JSON.parse(event.body as string);
  let errorMessage = '';
  if (!Array.isArray(entries)) {
    errorMessage = 'Input must be an array of dictionary entry objects';
  } else if (entries.length > DB_MAX_UPDATES_PER_CALL) {
    errorMessage = `Input cannot be more than ${DB_MAX_UPDATES_PER_CALL} entries per API invocation`;
  } else if (entries.find(entry => typeof entry !== 'object')) {
    errorMessage = 'Each dictionary entry must be a valid JSON object';
  } else if (entries.find(entry => !('guid' in entry && entry.guid))) {
    errorMessage = 'Each dictionary entry must have guid as a globally unique identifier';
  } else if (
    entries.find(
      entry =>
        !('data' in entry) || typeof entry.data !== 'object' || !Object.keys(entry.data).length,
    )
  ) {
    errorMessage = 'Each dictionary entry must have a non-empty data object';
  }

  if (errorMessage) {
    const errorType = Response.INVALID_REQUEST;
    return callback(null, Response.badRequest({ errorType, errorMessage }));
  }

  try {
    dbClient = await connectToDB();
    const db = dbClient.db(DB_NAME);

    await db.collection(COLLECTION_ENTRIES).createIndex(
      {
        'mainHeadWord.value': 'text',
        'senses.definitionOrGloss.value': 'text',
      },
      { name: 'wordsFulltextIndex', default_language: 'none' },
    );

    const updatedAt = new Date().toUTCString();
    const promises = entries.map(
      (entry: LoadEntry): Promise<UpdateWriteOpResult> => {
        const _id = entry.guid;
        return db
          .collection(COLLECTION_ENTRIES)
          .updateOne({ _id }, { $set: { _id, ...entry.data, updatedAt } }, { upsert: true });
      },
    );

    const dbResults: UpdateWriteOpResult[] = await Promise.all(promises);

    const updatedCount = dbResults
      .filter(result => result.modifiedCount)
      .reduce((total, result) => total + result.modifiedCount, 0);

    const insertedIds = dbResults
      .filter(result => result.upsertedCount)
      .map(result => result.upsertedId._id);

    const loadResult: LoadResult = {
      updatedAt,
      updatedCount,
      insertedCount: insertedIds.length,
      insertedGUIDs: insertedIds,
    };

    return callback(null, Response.success({ ...loadResult }));
  } catch (error) {
    // eslint-disable-next-line no-console
    console.log(error);
    return callback(null, Response.failure({ errorType: error.name, errorMessage: error.message }));
  }
}

export default handler;