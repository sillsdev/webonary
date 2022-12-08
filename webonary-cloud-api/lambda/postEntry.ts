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
 * @apiParam (Post Body) {Object[]} body Array of dictionary entries
 * @apiParam (Post Body) {String} body.guid GUID of the entry
 * @apiParam (Post Body) {String} body.dictionaryId Unique code for dictionary
 * @apiParam (Post Body) {String} body.letterHead Letter that this entry should be listed under
 * @apiParam (Post Body) {Object[]} body.mainHeadWord Array of Entry head word data
 * @apiParam (Post Body) {String} body.mainHeadWord.lang ISO language code for the head word
 * @apiParam (Post Body) {String} body.mainHeadWord.value ISO head word
 * @apiParam (Post Body) {Object} body.audio Audio associated with the entry
 * @apiParam (Post Body) {String} body.audio.fileClass Css class for the audio
 * @apiParam (Post Body) {String} body.audio.id Unique id for audio file
 * @apiParam (Post Body) {String} body.audio.src Relative file path to the audio
 * @apiParam (Post Body) {Object[]} body.pictures Images associated with the entry
 * @apiParam (Post Body) {String} body.pictures.caption Image caption
 * @apiParam (Post Body) {String} body.pictures.id Unique id for the image
 * @apiParam (Post Body) {String} body.pictures.src Relative file path to the image
 * @apiParam (Post Body) {Object[]} body.pronunciations Pronunciation guides associated with the entry
 * @apiParam (Post Body) {String} body.pronunciations.lang ISO language code for pronunciation
 * @apiParam (Post Body) {String} body.pronunciations.type Type of pronunciation
 * @apiParam (Post Body) {String} body.pronunciations.value Pronunciation phonetic guide
 * @apiParam (Post Body) {Object[]} body.reversalLetterHeads Reversal entry head letters
 * @apiParam (Post Body) {String} body.reversalLetterHeads.lang ISO language code for the reversal entry
 * @apiParam (Post Body) {String} body.reversalLetterHeads.value Reversal entry word head letter
 * @apiParam (Post Body) {Object[]} body.senses Senses for this entry
 * @apiParam (Post Body) {Object[]} body.senses.definitionOrGloss Definition or gloss for the entry
 * @apiParam (Post Body) {String} body.senses.definitionOrGloss.lang ISO language code
 * @apiParam (Post Body) {String} body.senses.definitionOrGloss.value Definition or the gloss
 * @apiParam (Post Body) {Object} body.senses.partOfSpeech Part of speech for this sense
 * @apiParam (Post Body) {String} body.senses.partOfSpeech.lang ISO language code
 * @apiParam (Post Body) {String} body.senses.partOfSpeech.value Part of speech abbreviation
 * @apiParam (Post Body) {Object[]} body.senses.semanticDomains Semantic Domains used in dictionary entries (language specific)
 * @apiParam (Post Body) {String} body.senses.semanticDomains.key Hierarchical code
 * @apiParam (Post Body) {String} body.senses.semanticDomains.lang ISO language code
 * @apiParam (Post Body) {String} body.senses.semanticDomains.value Semantic domain name
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
 *       "guid": "f9ae73a3-7b28-4fd3-bf89-2b23358b61c6"
 *       "dictionaryId": "moore",
 *       "letterHead": "ã",
 *       "mainHeadWord": [
 *         {
 *           "lang": "mos",
 *           "value": "ãadga"
 *         }
 *       ],
 *       "audio": {
 *         "fileClass": "mos-Zxxx-x-audio",
 *         "id": "g635754050803599765ãadga",
 *         "src": "AudioVisual/635754050803599765ãadga.mp3"
 *       },
 *       "pictures": [
 *         {
 *           "caption": "ãadga",
 *           "id": "g8086aade-8416-4cc6-8bba-f8f8a8d54a4d",
 *           "src": "pictures/Vitex_doniana.jpg"
 *         }
 *       ],
 *       "pronunciations": [
 *         {
 *           "lang": "mos",
 *           "type": "form",
 *           "value": "ã́-á"
 *         }
 *       ],
 *       "reversalLetterHeads": [
 *         {
 *           "lang": "fr",
 *           "value": "p"
 *         },
 *         {
 *           "lang": "en",
 *           "value": "b"
 *         }
 *       ],
 *       "senses": [
 *         {
 *           "definitionOrGloss": [
 *             {
 *               "lang": "fr",
 *               "value": "prunier noir"
 *             },
 *             {
 *               "lang": "en",
 *               "value": "blackberry tree, plum tree"
 *             }
 *           ],
 *           "partOfSpeech": {
 *             "lang": "fr",
 *             "value": "n"
 *           }
 *         }
 *         "semanticDomains": [
 *           {
 *             "key": "9",
 *             "lang": "fr",
 *             "value": "La Grammaire"
 *           },
 *           {
 *             "key": "9",
 *             "lang": "en",
 *             "value": "Grammar"
 *           }
 *         ]
 *       ]
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

import { APIGatewayEvent, APIGatewayProxyResult } from 'aws-lambda';
import { MongoClient, UpdateResult } from 'mongodb';
import { compile as compileHtmlToText } from 'html-to-text';
import { connectToDB } from './mongo';
import {
  MONGO_DB_NAME,
  DB_COLLECTION_DICTIONARY_ENTRIES,
  DB_COLLECTION_REVERSAL_ENTRIES,
  DB_MAX_UPDATES_PER_CALL,
} from './db';
import { PostResult } from './base.model';
import {
  DictionaryEntryItem,
  ReversalEntryItem,
  EntryItemType,
  ENTRY_TYPE_REVERSAL,
  EntryValueItem,
} from './entry.model';
import { copyObjectIgnoreKeyCase, getBasicAuthCredentials } from './utils';
import * as Response from './response';

let dbClient: MongoClient;

/**
 * Removes any HTML from a string.
 */
const stripHtml = compileHtmlToText({
  selectors: [
    // don't display href attributes of links
    { selector: 'a', format: 'inline' },
    // treat span tags as word separators
    { selector: 'span', format: 'paragraph' },
  ],
});

/**
 * Fills in empty DictionaryEntry fields from other fields that were supplied.
 */
// eslint-disable-next-line @typescript-eslint/no-explicit-any
function fillDictionaryEntryFields(source: any, destination: DictionaryEntryItem): void {
  // eslint-disable-next-line no-param-reassign
  destination.mainHeadWord = destination.mainHeadWord.filter((word) => word.value);
  if (destination.mainHeadWord.length === 0 && source.headword && source.headword.length > 0) {
    // eslint-disable-next-line no-param-reassign
    destination.mainHeadWord = source.headword.map((word: never) =>
      copyObjectIgnoreKeyCase(new EntryValueItem(), word),
    );
  }
}
/* eslint-enable no-param-reassign */

export async function upsertEntries(
  postedEntries: Array<object>,
  isReversalEntry: boolean,
  dictionaryId: string,
  username: string,
): Promise<{ dbResults: UpdateResult[]; updatedAt: string }> {
  const updatedAt = new Date().toUTCString();
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const entries: EntryItemType[] = postedEntries.map((postedEntry: any) => {
    const { guid } = postedEntry;
    const entry = isReversalEntry
      ? new ReversalEntryItem(guid, dictionaryId, username, updatedAt)
      : new DictionaryEntryItem(guid, dictionaryId, username, updatedAt);
    Object.assign(entry, copyObjectIgnoreKeyCase(entry, postedEntry));
    if (entry instanceof DictionaryEntryItem) {
      fillDictionaryEntryFields(postedEntry, entry);
    }
    entry.displayText = stripHtml(entry.displayXhtml);
    return entry;
  });

  dbClient = await connectToDB();
  const db = dbClient.db(MONGO_DB_NAME);
  const dbCollection = isReversalEntry
    ? DB_COLLECTION_REVERSAL_ENTRIES
    : DB_COLLECTION_DICTIONARY_ENTRIES;

  const promises = entries.map((entry: EntryItemType): Promise<UpdateResult> => {
    return db
      .collection(dbCollection)
      .updateOne({ _id: entry._id }, { $set: entry }, { upsert: true });
  });

  const dbResults: UpdateResult[] = await Promise.all(promises);
  return { updatedAt, dbResults };
}

export async function handler(event: APIGatewayEvent): Promise<APIGatewayProxyResult> {
  const authHeaders = event.headers?.Authorization;
  const dictionaryId = event.pathParameters?.dictionaryId?.toLowerCase();
  const isReversalEntry = event.queryStringParameters?.entryType === ENTRY_TYPE_REVERSAL;
  const eventBody = event.body;
  if (!dictionaryId || !authHeaders) {
    return Response.badRequest('Invalid parameters');
  }

  const { username } = getBasicAuthCredentials(authHeaders);

  const postedEntries = JSON.parse(eventBody as string);

  let errorMessage = '';
  if (!Array.isArray(postedEntries)) {
    errorMessage = 'Input must be an array of dictionary entry objects';
  } else if (postedEntries.length > DB_MAX_UPDATES_PER_CALL) {
    errorMessage = `Input cannot be more than ${DB_MAX_UPDATES_PER_CALL} entries per API invocation`;
  } else if (postedEntries.find((entry) => typeof entry !== 'object')) {
    errorMessage = 'Each dictionary entry must be a valid JSON object';
  } else if (postedEntries.find((entry) => !('guid' in entry && entry.guid))) {
    errorMessage = 'Each dictionary entry must have guid as a globally unique identifier';
  }

  if (errorMessage) {
    return Response.badRequest(errorMessage);
  }

  const { updatedAt, dbResults } = await upsertEntries(
    postedEntries,
    isReversalEntry,
    dictionaryId,
    username,
  );

  const updatedCount = dbResults
    .filter((result) => result.modifiedCount)
    .reduce((total, result) => total + result.modifiedCount, 0);

  const insertedIds = dbResults
    .filter((result) => result.upsertedCount)
    .map((result) => result.upsertedId.toString());

  const postResult: PostResult = {
    updatedAt,
    updatedCount,
    insertedCount: insertedIds.length,
    insertedIds: insertedIds.map((objectId) => objectId.toString()),
  };

  return Response.success(postResult);
}

export default handler;
