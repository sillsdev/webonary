/* eslint-disable @typescript-eslint/no-explicit-any */
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
 * @apiParam (Post Body) {Object[]} body.mainheadword Array of Entry head word data
 * @apiParam (Post Body) {String} body.mainheadword.lang ISO language code for the head word
 * @apiParam (Post Body) {String} body.mainheadword.value ISO head word
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
 * @apiParam (Post Body) {Object[]} body.senses.definitionorgloss Definition or gloss for the entry
 * @apiParam (Post Body) {String} body.senses.definitionorgloss.lang ISO language code
 * @apiParam (Post Body) {String} body.senses.definitionorgloss.value Definition or the gloss
 * @apiParam (Post Body) {Object} body.senses.partofspeech Part of speech for this sense
 * @apiParam (Post Body) {String} body.senses.partofspeech.lang ISO language code
 * @apiParam (Post Body) {String} body.senses.partofspeech.value Part of speech abbreviation
 * @apiParam (Post Body) {Object[]} body.senses.semanticdomains Semantic Domains used in dictionary entries (language specific)
 * @apiParam (Post Body) {String} body.senses.semanticdomains.key Hierarchical code
 * @apiParam (Post Body) {String} body.senses.semanticdomains.lang ISO language code
 * @apiParam (Post Body) {String} body.senses.semanticdomains.value Semantic domain name
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
 *       "mainheadword": [
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
 *           "definitionorgloss": [
 *             {
 *               "lang": "fr",
 *               "value": "prunier noir"
 *             },
 *             {
 *               "lang": "en",
 *               "value": "blackberry tree, plum tree"
 *             }
 *           ],
 *           "partofspeech": {
 *             "lang": "fr",
 *             "value": "n"
 *           }
 *         }
 *         "semanticdomains": [
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
import { load } from 'cheerio';
import { MongoClient } from 'mongodb';

import { connectToDB } from './mongo';
import {
  MONGO_DB_NAME,
  DB_COLLECTION_REVERSALS,
  DB_MAX_UPDATES_PER_CALL,
  dbCollectionEntries,
  reversalEntryId,
} from './db';
import { PostResult } from './base.model';
import { ENTRY_TYPE_REVERSAL, DbPaths, EntryType } from './entry.model';
import {
  getBasicAuthCredentials,
  isMaintenanceMode,
  maintenanceModeMessage,
  removeDiacritics,
} from './utils';
import * as Response from './response';

let dbClient: MongoClient;

/**
 * Searches for language texts and strips HTML and returns an object of text strings keyed by language
 */
const getLangTexts = (xhtml: string) => {
  const $ = load(xhtml);

  const langTexts: Record<string, string[]> = {};
  const langUnaccentedTexts: Record<string, string[]> = {};
  const searchTexts: string[] = [];

  // eslint-disable-next-line array-callback-return
  $('span[lang]').each((index, elem) => {
    const lang = $(elem).attr('lang');
    const text = $(elem).text();
    if (!lang || !text) {
      return;
    }

    const parent = $(elem).parent();
    if (
      parent.hasClass('graminfoabbrev') ||
      parent.hasClass('partofspeech') ||
      parent.hasClass('ownertype_abbreviation') ||
      parent.hasClass('reverseabbr') ||
      parent.parent().hasClass('semanticdomains')
    ) {
      return;
    }

    // MongoDb does now allow diacritic insensitive search, except in text search, i.e. ENTRY_SEARCH_TEXTS
    // So we search for any word with diacritics and then strip them and store them separately for use later
    const unaccentedText = removeDiacritics(text);

    if (langTexts[lang]) {
      if (!langTexts[lang].includes(text)) {
        langTexts[lang].push(text);
      }

      if (!langUnaccentedTexts[lang].includes(unaccentedText)) {
        langUnaccentedTexts[lang].push(unaccentedText);
      }
    } else {
      langTexts[lang] = [text];
      langUnaccentedTexts[lang] = [unaccentedText];
    }

    if (!searchTexts.includes(text)) {
      searchTexts.push(text);
    }
  });

  return { langTexts, langUnaccentedTexts, searchTexts };
};

interface TransformPostedEntryParams {
  postedEntry: Record<string, any>;
  dictionaryId: string;
}

const transformToReversalEntry = ({
  postedEntry,
  dictionaryId,
}: TransformPostedEntryParams): EntryType => {
  return {
    ...postedEntry,
    _id: reversalEntryId({ dictionaryId, guid: postedEntry.guid }),
    guid: postedEntry.guid,
    dictionaryId,
  };
};

export const transformToEntry = ({
  postedEntry,
  dictionaryId,
}: TransformPostedEntryParams): EntryType => {
  const extraTexts: Record<string, string[] | Record<string, string[]>> = {};
  if (postedEntry.displayXhtml) {
    const { langTexts, langUnaccentedTexts, searchTexts } = getLangTexts(postedEntry.displayXhtml);
    if (searchTexts.length) {
      extraTexts[DbPaths.ENTRY_SEARCH_TEXTS] = searchTexts;
      extraTexts[DbPaths.ENTRY_LANG_TEXTS] = langTexts;
      extraTexts[DbPaths.ENTRY_LANG_UNACCENTED_TEXTS] = langUnaccentedTexts;
    }
  }

  return {
    ...postedEntry,
    _id: postedEntry.guid,
    guid: postedEntry.guid,
    dictionaryId,
    ...extraTexts,
  };
};

export async function upsertEntries(
  postedEntries: Array<any>,
  isReversal: boolean,
  dictionaryId: string,
  username: string,
) {
  const updatedAt = new Date();
  const transformEntryFunction = isReversal ? transformToReversalEntry : transformToEntry;
  const entries = postedEntries.map((postedEntry) => {
    const entry = transformEntryFunction({ postedEntry, dictionaryId });
    entry.updatedAt = updatedAt;
    entry.updatedBy = username;
    return entry;
  });

  // eslint-disable-next-line no-console
  console.log(`Converted first posted entry to `, JSON.stringify(entries[0]));

  dbClient = await connectToDB();
  const db = dbClient.db(MONGO_DB_NAME);
  const dbCollection = isReversal ? DB_COLLECTION_REVERSALS : dbCollectionEntries(dictionaryId);

  const promises = entries.map((entry) => {
    // reversal entries for all dictionaries are stored in a single collection
    return db.collection(dbCollection).replaceOne({ _id: entry._id }, entry, { upsert: true });
  });

  const dbResults = await Promise.all(promises);

  return { updatedAt: updatedAt.toUTCString(), dbResults };
}

export async function handler(event: APIGatewayEvent): Promise<APIGatewayProxyResult> {
  if (isMaintenanceMode()) {
    return Response.temporarilyUnavailable(maintenanceModeMessage());
  }

  const authHeaders = event.headers?.Authorization;
  const dictionaryId = event.pathParameters?.dictionaryId?.toLowerCase();
  const isReversal = event.queryStringParameters?.entryType === ENTRY_TYPE_REVERSAL;
  const eventBody = event.body;
  if (!dictionaryId || !authHeaders) {
    return Response.badRequest('Invalid parameters');
  }

  const { username } = getBasicAuthCredentials(authHeaders);
  const postedEntries = JSON.parse(eventBody as string);

  // eslint-disable-next-line no-console
  console.log(
    `Received request to post ${postedEntries.length} entries for ${dictionaryId} by user ${username}`,
    JSON.stringify(postedEntries[0]),
  );

  let errorMessage = '';
  if (!Array.isArray(postedEntries)) {
    errorMessage = 'Input must be an array of dictionary entry objects';
  } else if (postedEntries.length > DB_MAX_UPDATES_PER_CALL) {
    errorMessage = `Input cannot be more than ${DB_MAX_UPDATES_PER_CALL} entries per API invocation`;
  } else if (postedEntries.some((entry) => typeof entry !== 'object')) {
    errorMessage = 'Each dictionary entry must be a valid JSON object';
  } else if (postedEntries.some((entry) => !('guid' in entry && entry.guid))) {
    errorMessage = 'Each dictionary entry must have guid as a globally unique identifier';
  }

  if (errorMessage) {
    return Response.badRequest(errorMessage);
  }

  const { updatedAt, dbResults } = await upsertEntries(
    postedEntries,
    isReversal,
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

  // eslint-disable-next-line no-console
  console.log(
    `Sending results for posting ${
      isReversal ? 'reversal' : ''
    } entries to dictionary ${dictionaryId}`,
    postResult,
  );
  return Response.success(postResult);
}

export default handler;
