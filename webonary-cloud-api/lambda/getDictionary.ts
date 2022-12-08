/**
 * @api {get} /get/dictionary/:dictionaryId Get dictionary
 * @apiDescription Gets the metadata of a dictionary.
 * @apiName GetDictionary
 * @apiGroup Dictionary
 *
 * @apiSuccess {String} _id Id of the dictionary
 *
 * @apiSuccess {Object} mainLanguage Dictionary language metadata
 * @apiSuccess {String} mainLanguage.lang ISO language code
 * @apiSuccess {String} mainLanguage.title ISO language name
 * @apiSuccess {String[]} mainLanguage.letters ISO Letters for the language
 * @apiSuccess {String[]} mainLanguage.cssFiles Css files used to displaying entries from this language (in order)
 * @apiSuccess {Number} mainLanguage.entriesCount Number of entries in this dictionary
 *
 * @apiSuccess {String[]} definitionOrGlossLangs Distinct language codes used in definitions
 *
 * @apiSuccess {Object[]} partsOfSpeech Parts of speech short codes for this language
 * @apiSuccess {String} partsOfSpeech.lang ISO language code
 * @apiSuccess {String} partsOfSpeech.abbreviation Abbreviation of this part of speech
 * @apiSuccess {String} partsOfSpeech.name Name of this part of speech
 * @apiSuccess {String} partsOfSpeech.guid
 *
 * @apiSuccess {Object[]} reversalLanguages Reversal languages defined for the main language
 * @apiSuccess {String} reversalLanguages.lang ISO language code
 * @apiSuccess {String} reversalLanguages.title ISO language name
 * @apiSuccess {String[]} reversalLanguages.letters ISO Letters for the language
 * @apiSuccess {String[]} reversalLanguages.cssFiles Css files used to displaying entries from this language (in order)
 * @apiSuccess {Number} reversalLanguages.entriesCount Number of reversal entries for this reversal language and dictionary
 *
 * @apiSuccess {Object[]} semanticDomains Semantic Domains used in dictionary entries (language specific)
 * @apiSuccess {String} semanticDomains.lang ISO language code
 * @apiSuccess {String} semanticDomains.abbreviation Abbreviation of this semantic domain
 * @apiSuccess {String} semanticDomains.name Name of this semantic domain
 * @apiSuccess {String} semanticDomains.guid
 * @apiSuccess {String} semanticDomains.nameInsensitive Lowercase name of this semantic domain
 *
 * @apiSuccess {Date} updatedAt Time (UTC) that the dictionary metadata was last updated. Updates to the dictionary and
 * reversal entries do not count. See
 * <a href="https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/toUTCString">
 *     Date.prototype.toUTCString()</a> for format details.
 * @apiSuccess {String} updatedBy Username of the person who performed the last update to the dictionary metadata.
 * Updates to the dictionary and reversal entries do not count.
 *
 * @apiError (404) NotFound Cannot find a dictionary with the supplied id.
 */

import { APIGatewayEvent, APIGatewayProxyResult } from 'aws-lambda';
import { MongoClient } from 'mongodb';
import { connectToDB } from './mongo';
import {
  MONGO_DB_NAME,
  DB_COLLECTION_DICTIONARIES,
  DB_COLLECTION_DICTIONARY_ENTRIES,
  DB_COLLECTION_REVERSAL_ENTRIES,
} from './db';
import { Dictionary } from './dictionary.model';
import { DbPaths } from './entry.model';

import { DbFindParameters } from './base.model';
import * as Response from './response';

let dbClient: MongoClient;

export async function handler(event: APIGatewayEvent): Promise<APIGatewayProxyResult> {
  const dictionaryId = event.pathParameters?.dictionaryId?.toLowerCase();
  const dbFind: DbFindParameters = { dictionaryId };

  dbClient = await connectToDB();
  const db = dbClient.db(MONGO_DB_NAME);
  const dbItem: Dictionary | null = await db
    .collection<Dictionary>(DB_COLLECTION_DICTIONARIES)
    .findOne({ _id: dictionaryId });

  if (!dbItem) {
    return Response.notFound();
  }

  // get unique language codes from definitionOrGloss
  // TODO: Populate this during dictionary upload
  dbItem.definitionOrGlossLangs = await db
    .collection(DB_COLLECTION_DICTIONARY_ENTRIES)
    .distinct(DbPaths.ENTRY_DEFINITION_LANG, dbFind);

  // get total entries
  // TODO: Populate this during dictionary upload
  dbItem.mainLanguage.entriesCount = await db
    .collection(DB_COLLECTION_DICTIONARY_ENTRIES)
    .countDocuments(dbFind);

  // get reversal entry counts
  // TODO: Populate this during dictionary upload

  const reversalEntriesCounts = await Promise.all(
    dbItem.reversalLanguages.map(async ({ lang }) => {
      dbFind[DbPaths.ENTRY_REVERSAL_FORM_LANG] = lang;
      return db.collection(DB_COLLECTION_REVERSAL_ENTRIES).countDocuments(dbFind);
    }),
  );

  reversalEntriesCounts.forEach((entriesCount, index) => {
    dbItem.reversalLanguages[index].entriesCount = entriesCount;
  });

  return Response.success(dbItem);
}

export default handler;
