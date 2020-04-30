/**
 * @apiDefine DictionaryPostBody
 * 
 * @apiParam (Post Body) {Object[]} body       Array of dictionary entries
 * @apiParam (Post Body) {String}   body.guid  GUID of the entry
 * @apiParam (Post Body) {Object}   body.data  Object of entry data
 *
 */ 

/**
 * @api {post} /load/dictionary/:dictionaryId Load dictionary
 * @apiDescription Calling this API will insert or update metadata for a dictionary
 * @apiName LoadDictionary
 * @apiGroup Dictionary
 * @apiPermission dictionary admin in Webonary
 *
 * @apiUse BasicAuthHeader
 *
 * @apiUse DictionaryIdPath
 *
 * @apiUse DictionaryPostBody
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
 *        "dictionary": "moore",
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
 *        "reverseLetterHeads": [
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
 *        "dictionary": "moore",
 *        "letterHead": "a",
 *        "mainHeadWord": [
 *          {
 *            "lang": "mos",
 *            "value": "abada"
 *          }
 *        ],
 *        "pictures": [],
 *        "pronunciations": [],
 *        "reverseLetterHeads": [
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
 * @apiSuccess {String} updatedAt Timestamp of the loading of dictionary metadata in GMT
 * @apiSuccess {Number} updatedCount Dictionary updated
 * @apiSuccess {Number} insertedCount Dictionary inserted
 *
 * @apiSuccessExample Success Response Example
 * HTTP/1.1 200 OK
 * {
 *    "updatedAt": "Thu, 23 Apr 2020 17:00:15 GMT",
 *    "updatedCount": 0,
 *    "insertedCount": 1
 * }
 *
 * @apiError BadRequest Input should be an array of up to 50 entry objects.
 *
 * @apiErrorExample {json} BadRequest Example
 * HTTP/1.1 400 BadRequest
 * {
 *    "ErrorType": "BadRequest",
 *    "Message": "Input must be an array of entries"
 * }
 *
 * @apiUse ErrorForbidden
 *
 */

import { APIGatewayEvent, Callback, Context } from 'aws-lambda';
import { MongoClient, ObjectId, UpdateWriteOpResult } from 'mongodb';
import { connectToDB } from './mongo';
import { DB_NAME, COLLECTION_DICTIONARIES, LoadDictionary } from './db';
import * as Response from './response';

interface LoadResult {
  updatedAt: string;
  updatedCount: number,
  insertedCount: number,
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
    const dictionary: LoadDictionary = JSON.parse(event.body as string);
    const _id = dictionary.id;
    const updatedAt = new Date().toUTCString();

    dbClient = await connectToDB();
    const db = dbClient.db(DB_NAME);

  
    const dbResult = await db
      .collection(COLLECTION_DICTIONARIES)
      .updateOne({ _id }, { $set: { _id, ...dictionary.data, updatedAt } }, { upsert: true });

    const loadResult: LoadResult = {
      updatedAt,
      updatedCount: dbResult.modifiedCount,
      insertedCount: dbResult.upsertedCount,
    };

    return callback(null, Response.success({ ...loadResult }));
  } catch (error) {
    // eslint-disable-next-line no-console
    console.log(error);
    return callback(null, Response.failure({ errorType: error.name, errorMessage: error.message }));
  }
}

export default handler;
