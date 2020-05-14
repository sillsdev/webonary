/**
 * @apiDefine DictionaryPostBody
 *
 * @apiParam (Post Body) {String} _id Dictionary id (unique short name)
 *
 * @apiParam (Post Body) {Object} mainLanguage Dictionary language metadata
 * @apiParam (Post Body) {String} mainLanguage.lang ISO language code
 * @apiParam (Post Body) {String} mainLanguage.title ISO language name
 * @apiParam (Post Body) {String[]} mainLanguage.letters ISO Letters for the language
 * @apiParam (Post Body) {String[]} mainLanguage.partsOfSpeech Parts of speech short codes for this language
 * @apiParam (Post Body) {String[]} mainLanguage.cssFiles Css files used to displaying entries from this language (in order)
 *
 * @apiParam (Post Body) {Object[]} reversalLanguages Reversal languages defined for the main language
 * @apiParam (Post Body) {String} reversalLanguages.lang ISO language code
 * @apiParam (Post Body) {String} reversalLanguages.title ISO language name
 * @apiParam (Post Body) {String[]} reversalLanguages.letters ISO Letters for the language
 * @apiParam (Post Body) {String[]} reversalLanguages.partsOfSpeech Parts of speech short codes for this language
 * @apiParam (Post Body) {String[]} reversalLanguages.cssFiles Css files used to displaying entries from this language (in order)
 *
 * @apiParam (Post Body) {Object[]} semanticDomains Semantic Domains used in dictionary entries (language specific)
 * @apiParam (Post Body) {String} semanticDomains.key Hierarchical code
 * @apiParam (Post Body) {String} semanticDomains.lang ISO language code
 * @apiParam (Post Body) {String} semanticDomains.value Semantic domain name
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
 *    "_id": "moore",
 *    "mainLanguage": {
 *      "lang": "mos",
 *      "title": "Moore",
 *      "letters": ["a", "ã", "b", "d"],
 *      "partsOfSpeech": ["adv", "n", "v"],
 *      "cssFiles": [
 *        "configured.css",
 *        "ProjectDictionaryOverrides.css"
 *      ]
 *    },
 *    "reversalLanguages": [
 *      {
 *        "lang": "fr",
 *        "title": "French"
 *        "letters": ["c", "ç", "d", "e", "z"],
 *        "partsOfSpeech": [],
 *        "cssFiles": [
 *          "reversal_fr.css"
 *         ]
 *      },
 *      {
 *         "lang": "en",
 *         "title": "English",
 *         "letters": ["a", "x", "y", "z"],
 *         "partsOfSpeech": [],
 *         "cssFiles": [
 *           "reversal_en.css"
 *         ]
 *      }
 *    ],
 *    "semanticDomains": [
 *      {
 *        "key": "9",
 *        "lang": "fr",
 *        "value": "La Grammaire",
 *      },
 *      {
 *        "key": "9",
 *        "lang": "en",
 *        "value": "Grammar",
 *      }
 *    ]
 *  }
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
  Dictionary,
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
    const dictionary: Dictionary = JSON.parse(event.body as string);
    const _id = dictionaryId;

    // set searchable value for each semantic domain value
    if (dictionary.semanticDomains) {
      dictionary.semanticDomains = setSearchableEntries(dictionary.semanticDomains);
    }
    const updatedAt = new Date().toUTCString();

    dictionary.updatedAt = updatedAt;

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
      .updateOne({ _id }, { $set: dictionary }, { upsert: true });

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
