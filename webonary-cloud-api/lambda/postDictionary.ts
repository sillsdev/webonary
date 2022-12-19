/**
 * @apiDefine DictionaryPostBody
 *
 * @apiParam (Post Body) {String} id Dictionary id (unique short name)
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
 *    "id": "moore",
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

import axios from 'axios';
import { APIGatewayEvent, APIGatewayProxyResult } from 'aws-lambda';
import { MongoClient, UpdateResult } from 'mongodb';

import { connectToDB } from './mongo';
import { MONGO_DB_NAME, DB_COLLECTION_DICTIONARIES } from './db';
import { PostResult } from './base.model';
import { DictionaryItem } from './dictionary.model';
import { copyObjectIgnoreKeyCase, setSearchableEntries, getBasicAuthCredentials } from './utils';
import * as Response from './response';

let dbClient: MongoClient;

export async function upsertDictionary(
  eventBody: string | null,
  dictionaryId: string,
  username: string,
): Promise<{ dbResult: UpdateResult; updatedAt: string }> {
  const updatedAt = new Date();

  const posted = JSON.parse(eventBody as string);
  let dictionaryItem = new DictionaryItem(dictionaryId, username, updatedAt);
  dictionaryItem = Object.assign(dictionaryItem, copyObjectIgnoreKeyCase(dictionaryItem, posted));
  if (dictionaryItem.semanticDomains) {
    dictionaryItem.semanticDomains = setSearchableEntries(dictionaryItem.semanticDomains);
  }
  dbClient = await connectToDB();
  const db = dbClient.db(MONGO_DB_NAME);

  const dbResult = await db
    .collection(DB_COLLECTION_DICTIONARIES)
    .updateOne({ _id: dictionaryId }, { $set: dictionaryItem }, { upsert: true });
  return { updatedAt: updatedAt.toUTCString(), dbResult };
}

export async function handler(event: APIGatewayEvent): Promise<APIGatewayProxyResult> {
  const authHeaders = event.headers?.Authorization;
  const dictionaryId = event.pathParameters?.dictionaryId?.toLowerCase();
  const eventBody = event.body;
  if (!dictionaryId || !authHeaders) {
    return Response.badRequest('Invalid parameters');
  }

  const auth = getBasicAuthCredentials(authHeaders);

  // eslint-disable-next-line no-console
  console.log(
    `Received request to post dictionary ${dictionaryId} by user ${auth.username}`,
    eventBody,
  );
  const { updatedAt, dbResult } = await upsertDictionary(eventBody, dictionaryId, auth.username);

  // Call Webonary to alert that dictionary data is ready and refreshed
  axios.defaults.headers.post['Content-Type'] = 'application/json';
  const resetPath = `${process.env.WEBONARY_URL}/${dictionaryId}${process.env.WEBONARY_RESET_DICTIONARY_PATH}`;
  let message = '';

  try {
    const response = await axios.post(resetPath, '{}', { auth });

    if (response.status === 200 && response.data) {
      message = response.data;
    }
  } catch (error) {
    // eslint-disable-next-line no-console
    console.log(error);
    message = JSON.stringify(error);
  }

  const postResult: PostResult = {
    updatedAt,
    updatedCount: dbResult.modifiedCount,
    insertedCount: dbResult.upsertedCount,
    insertedIds: [dictionaryId],
    message,
  };

  // eslint-disable-next-line no-console
  console.log(`Sending result for dictionary ${dictionaryId}`, postResult);
  return Response.success(postResult);
}

export default handler;
