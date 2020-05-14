/**
 * @apiDefine DictionaryPostBody
 *
 * @apiParam (Post Body) {Object} body      JSON representation of dictionary metadata
 * @apiParam (Post Body) {String} body.id   Dictionary id (unique short name)
 * @apiParam (Post Body) {Object} body.data Dictionary metadata
 *
 */

/**
 * @api {post} /post/dictionary/:dictionaryId Post dictionary
 * @apiDescription Calling this API will insert or update metadata for a dictionary
 * @apiName PostDictionary
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
 * {
 *    "id": "moore",
 *    "data": {
 *      "mainLanguage": {
 *        "lang": "mos",
 *        "title": "Moore",
 *        "letters": ["a", "ã", "b", "d"],
 *        "cssFiles": [
 *          "configured.css",
 *          "ProjectDictionaryOverrides.css"
 *        ]
 *      },
 *      "reversalLanguages": [
 *        {
 *          "lang": "fr",
 *          "title": "French"
 *          "letters": ["c", "ç", "d", "e", "z"],
 *          "cssFiles": [
 *            "reversal_fr.css"
 *           ]
 *        },
 *        {
 *           "lang": "en",
 *           "title": "English",
 *           "letters": ["a", "x", "y", "z"],
 *           "cssFiles": [
 *             "reversal_en.css"
 *           ]
 *        }
 *      ]
 *    }
 * }
 *
 * @apiSuccess {String} updatedAt Timestamp of the posting of dictionary metadata in GMT
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
 * @apiUse BadRequest
 * @apiUse ErrorForbidden
 * @apiUse SyntaxError
 * @apiUse TypeError
 *
 */

import { APIGatewayEvent, Callback, Context } from 'aws-lambda';
import { MongoClient } from 'mongodb';
import { connectToDB } from './mongo';
import {
  DB_NAME,
  DB_COLLECTION_DICTIONARIES,
  DB_COLLECTION_ENTRIES,
  DB_COLLATION_LOCALE_DEFAULT_FOR_INSENSITIVITY,
  DB_COLLATION_STRENGTH_FOR_INSENSITIVITY,
  PATH_TO_ENTRY_MAIN_HEADWORD_LANG,
  PATH_TO_ENTRY_MAIN_HEADWORD_VALUE,
  PATH_TO_ENTRY_DEFINITION_VALUE,
  PostDictionary,
  setSearchableEntries,
} from './db';
import * as Response from './response';

interface PostResult {
  updatedAt: string;
  updatedCount: number;
  insertedCount: number;
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
    const dictionaryId = event.pathParameters?.dictionaryId ?? '';
    const dictionary: PostDictionary = JSON.parse(event.body as string);
    const _id = dictionaryId;
    const updatedAt = new Date().toUTCString();

    const processedData = dictionary.data;

    // set searchable value for each semantic domain value
    if (processedData.semanticDomains) {
      processedData.semanticDomains = setSearchableEntries(processedData.semanticDomains);
    }

    dbClient = await connectToDB();
    const db = dbClient.db(DB_NAME);

    // fulltext index (case and diacritic insensitive by default)
    await db.collection(DB_COLLECTION_ENTRIES).createIndex(
      {
        [PATH_TO_ENTRY_MAIN_HEADWORD_VALUE]: 'text',
        [PATH_TO_ENTRY_DEFINITION_VALUE]: 'text',
      },
      { name: 'wordsFulltextIndex', default_language: 'none' },
    );

    // case and diacritic insensitive index for semantic domains
    await db.collection(DB_COLLECTION_ENTRIES).createIndex(
      {
        [PATH_TO_ENTRY_MAIN_HEADWORD_LANG]: 1,
        [PATH_TO_ENTRY_MAIN_HEADWORD_VALUE]: 1,
      },
      {
        collation: {
          locale: DB_COLLATION_LOCALE_DEFAULT_FOR_INSENSITIVITY,
          strength: DB_COLLATION_STRENGTH_FOR_INSENSITIVITY,
        },
      },
    );

    const dbResult = await db
      .collection(DB_COLLECTION_DICTIONARIES)
      .updateOne({ _id }, { $set: { _id, ...processedData, updatedAt } }, { upsert: true });

    const postResult: PostResult = {
      updatedAt,
      updatedCount: dbResult.modifiedCount,
      insertedCount: dbResult.upsertedCount,
    };

    return callback(null, Response.success({ ...postResult }));
  } catch (error) {
    // eslint-disable-next-line no-console
    console.log(error);
    return callback(null, Response.failure({ errorType: error.name, errorMessage: error.message }));
  }
}

export default handler;
