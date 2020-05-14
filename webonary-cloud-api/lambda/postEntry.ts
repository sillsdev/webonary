/**
 * @apiDefine BasicAuthHeader
 *
 * @apiHeader Authorization Basic Auth value corresponding to <a href=https://www.webonary.org>Webonary</a> dictionary site's admin username and password
 * @apiHeader Content-Type application/json
 * @apiHeaderExample {Header} Header Example
 * "Authorization: Basic YWRtaW46cGFzc3dvcmQ="
 */

/**
 * @apiDefine DictionaryIdPath
 *
 * @apiParam (Path) {String} :dictionaryId Unique dictionary id registered in <a href=https://www.webonary.org>Webonary</a>
 */

/**
 * @apiDefine DictionaryEntryPostBody
 *
 * @apiParam (Post Body) {Object[]} body       Array of dictionary entries
 * @apiParam (Post Body) {String}   body.guid  GUID of the entry
 * @apiParam (Post Body) {Object}   body.data  Object of entry data
 */

/**
 * @apiDefine BadRequest
 *
 * @apiError (400) BadRequest Input should be a valid JSON object for this API call
 *
 * @apiErrorExample {json} Bad Request Example
 * HTTP/1.1 400 Bad Request
 * {
 *    "errorType": "BadRequest",
 *    "errorMessage": "Input must be an array of entries"
 * }
 */

/**
 * @apiDefine ErrorForbidden
 *
 * @apiError (403) ErrorForbidden Incorrect user credentials or user is not authorized to post to the dictionary
 *
 * @apiErrorExample {json} ErrorForbidden
 * HTTP/1.1 403 Forbidden
 * {
 *    "Message": "User is not authorized to access this resource with an explicit deny"
 * }
 */

/**
 * @apiDefine TypeError
 *
 * @apiError (500) TypeError Invalid type in JSON body
 *
 * @apiErrorExample {json} TypeError Example
 * HTTP/1.1 500 Internal Server Error
 * {
 *    "errorType": "TypeError",
 *    "errorMessage": "Cannot read property 'id' of null"
 * }
 */

/**
 * @apiDefine SyntaxError
 *
 * @apiError (500) SyntaxError Invalid JSON body structure
 *
 * @apiErrorExample {json} SyntaxError Example
 * HTTP/1.1 500 Internal Server Error
 * {
 *    "errorType": "SyntaxError",
 *    "errorMessage": "Unexpected token } in JSON at position 243"
 * }
 */

/**
 * @api {post} /post/entry/:dictionaryId Post entry
 * @apiDescription Calling this API will allow posting of up to 50 dictionary entries. If the entry guid already exists, update will occur instead of an insert.
 * @apiName PostDictionaryEntry
 * @apiGroup Dictionary
 * @apiPermission dictionary admin in Webonary
 *
 * @apiUse BasicAuthHeader
 *
 * @apiUse DictionaryIdPath
 *
 * @apiUse DictionaryEntryPostBody
 *
 * @apiParamExample {json} Post Body Example
 * [
 *    {
 *      "data": {
 *        "audio": {
 *          "fileClass": "mos-Zxxx-x-audio",
 *          "id": "g635754050803599765ãadga",
 *          "src": "AudioVisual/635754050803599765ãadga.mp3"
 *        },
 *        "dictionaryId": "moore",
 *        "letterHead": "ã",
 *        "mainHeadWord": [
 *          {
 *            "lang": "mos",
 *            "value": "ãadga"
 *          }
 *        ],
 *        "pictures": [
 *          {
 *            "caption": "ãadga",
 *            "id": "g8086aade-8416-4cc6-8bba-f8f8a8d54a4d",
 *            "src": "pictures/Vitex_doniana.jpg"
 *          }
 *        ],
 *        "pronunciations": [
 *          {
 *            "lang": "mos",
 *            "type": "form",
 *            "value": "ã́-á"
 *          }
 *        ],
 *        "reversalLetterHeads": [
 *          {
 *            "lang": "fr",
 *            "value": "p"
 *          },
 *          {
 *            "lang": "en",
 *            "value": "b"
 *          }
 *        ],
 *        "senses": {
 *          "definitionOrGloss": [
 *            {
 *              "lang": "fr",
 *              "value": "prunier noir"
 *            },
 *            {
 *              "lang": "en",
 *              "value": "blackberry tree, plum tree"
 *            }
 *          ],
 *          "partOfSpeech": {
 *            "lang": "fr",
 *            "value": "n"
 *          }
 *        }
 *      },
 *      "guid": "06a3f6ba-759f-42f2-b284-b4d5b3c887a2"
 *    },
 *    {
 *      "data": {
 *        "audio": {
 *          "fileClass": "mos-Zxxx-x-audio",
 *          "id": "g636908699703911281abada",
 *          "src": "AudioVisual/636908699703911281abada.mp3"
 *        },
 *        "dictionaryId": "moore",
 *        "letterHead": "a",
 *        "mainHeadWord": [
 *          {
 *            "lang": "mos",
 *            "value": "abada"
 *          }
 *        ],
 *        "pictures": [],
 *        "pronunciations": [],
 *        "reversalLetterHeads": [
 *          {
 *            "lang": "fr",
 *            "value": "j"
 *          },
 *          {
 *            "lang": "en",
 *            "value": "n"
 *          }
 *        ],
 *        "senses": {
 *          "definitionOrGloss": [
 *            {
 *              "lang": "fr",
 *              "value": "jamais"
 *            },
 *            {
 *              "lang": "en",
 *              "value": "never"
 *            }
 *          ],
 *          "partOfSpeech": {
 *            "lang": "fr",
 *            "value": "adv"
 *          }
 *        }
 *      },
 *      "guid": "f9ae73a3-7b28-4fd3-bf89-2b23358b61c6"
 *    }
 * ]
 * @apiSuccess {String} updatedAt Timestamp of the posting of entries in GMT
 * @apiSuccess {Number} updatedCount Number of entries updated
 * @apiSuccess {Number} insertedCount Number of entries inserted
 * @apiSuccess {Object[]} insertedGUIDs Array containing GUID of the inserted entries
 *
 * @apiSuccessExample Success Response Example
 * HTTP/1.1 200 OK
 * {
 *    "updatedAt": "Thu, 23 Apr 2020 17:00:15 GMT",
 *    "updatedCount": 48,
 *    "insertedCount": 2,
 *    "insertedGUIDs": [
 *       "edea14f7-e59c-494c-b7c1-94e00f5f8a81",
 *       "496e6865-bf0d-40aa-9834-93b47404ed93"
 *    ]
 * }
 *
 * @apiUse BadRequest
 * @apiUse ErrorForbidden
 * @apiUse SyntaxError
 * @apiUse TypeError
 */

import { APIGatewayEvent, Callback, Context } from 'aws-lambda';
import { MongoClient, ObjectId, UpdateWriteOpResult } from 'mongodb';
import { connectToDB } from './mongo';
import { DB_NAME, DB_COLLECTION_ENTRIES, DB_MAX_UPDATES_PER_CALL, DictionaryEntry } from './db';
import * as Response from './response';

interface PostResult {
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

  try {
    const entries: DictionaryEntry[] = JSON.parse(event.body as string); // any type problems will be caught

    let errorMessage = '';
    if (!Array.isArray(entries)) {
      errorMessage = 'Input must be an array of dictionary entry objects';
    } else if (entries.length > DB_MAX_UPDATES_PER_CALL) {
      errorMessage = `Input cannot be more than ${DB_MAX_UPDATES_PER_CALL} entries per API invocation`;
    } else if (entries.find(entry => typeof entry !== 'object')) {
      errorMessage = 'Each dictionary entry must be a valid JSON object';
    } else if (entries.find(entry => !('guid' in entry && entry.guid))) {
      errorMessage = 'Each dictionary entry must have guid as a globally unique identifier';
    }

    if (errorMessage) {
      return callback(null, Response.badRequest(errorMessage));
    }

    dbClient = await connectToDB();
    const db = dbClient.db(DB_NAME);

    const updatedAt = new Date().toUTCString();

    const promises = entries.map(
      (entry: PostEntry): Promise<UpdateWriteOpResult> => {
        const _id = entry.guid;
        return db
          .collection(DB_COLLECTION_ENTRIES)
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

    const postResult: PostResult = {
      updatedAt,
      updatedCount,
      insertedCount: insertedIds.length,
      insertedGUIDs: insertedIds,
    };

    return callback(null, Response.success({ ...postResult }));
  } catch (error) {
    // eslint-disable-next-line no-console
    console.log(error);
    return callback(null, Response.failure({ errorType: error.name, errorMessage: error.message }));
  }
}

export default handler;
