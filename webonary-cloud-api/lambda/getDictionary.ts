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
 * @apiSuccess {String} partsOfSpeech.entriesCount Number of entries having this part of speech
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
 * @apiSuccess {String[]} semanticDomainAbbreviationsUsed Distinct semantic domain abbreviations codes used in senses
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

import {
  MONGO_DB_NAME,
  DB_COLLECTION_DICTIONARIES,
  DB_COLLECTION_REVERSALS,
  dbCollectionEntries,
} from './db';
import { Dictionary } from './dictionary.model';
import { DbPaths } from './entry.model';
import { connectToDB } from './mongo';
import * as Response from './response';

let dbClient: MongoClient;

export async function handler(event: APIGatewayEvent): Promise<APIGatewayProxyResult> {
  const dictionaryId = event.pathParameters?.dictionaryId?.toLowerCase();
  if (!dictionaryId) {
    return Response.badRequest('Dictionary must be in the path.');
  }

  // eslint-disable-next-line no-console
  console.log(`Getting dictionary ${dictionaryId}...`);

  dbClient = await connectToDB();
  const db = dbClient.db(MONGO_DB_NAME);
  const dbItem = await db
    .collection<Dictionary>(DB_COLLECTION_DICTIONARIES)
    .findOne({ _id: dictionaryId });

  if (!dbItem) {
    return Response.notFound();
  }

  // TODO: Populate these during dictionary upload
  const entriesCollection = db.collection(dbCollectionEntries(dictionaryId));

  // get total entries and semantic domains used in senses
  [dbItem.mainLanguage.entriesCount, dbItem.semanticDomainAbbreviationsUsed] = await Promise.all([
    entriesCollection.countDocuments(),
    entriesCollection.distinct(DbPaths.ENTRY_SEM_DOMS_ABBREV_VALUE),
  ]);

  dbItem.semanticDomainAbbreviationsUsed = Array.isArray(dbItem.semanticDomainAbbreviationsUsed)
    ? dbItem.semanticDomainAbbreviationsUsed.filter((abbreviation) => abbreviation !== '')
    : [];

  // get unique language codes from definitionorgloss
  const senseLangs = await Promise.all(
    [
      DbPaths.ENTRY_DEFINITION_OR_GLOSS_LANG,
      DbPaths.ENTRY_DEFINITION_LANG,
      DbPaths.ENTRY_GLOSS_LANG,
    ].map((key) => entriesCollection.distinct(key)),
  );
  dbItem.definitionOrGlossLangs = [...new Set(senseLangs.flat(1))].filter((lang) => lang !== '');

  // get parts of speech counts
  dbItem.partsOfSpeech =
    dbItem.partsOfSpeech
      ?.filter(
        // remove nulls
        (part) => part.lang && part.abbreviation,
      )
      ?.filter(
        // de-dup
        (part, index, self) =>
          index ===
          self.findIndex((p) => p.lang === part.lang && p.abbreviation === part.abbreviation),
      )
      ?.map((part) => {
        // For some reason, FLex sends these decomposed, but entries are composed (e.g. for accented chars)
        return { ...part, abbreviation: part.abbreviation.normalize('NFC') };
      }) ?? [];

  const partsOfSpeechCounts = await Promise.all(
    dbItem.partsOfSpeech.map(async ({ abbreviation }) => {
      return abbreviation
        ? entriesCollection.countDocuments({
            dictionaryId,
            $or: [
              { [DbPaths.ENTRY_PART_OF_SPEECH_VALUE]: abbreviation },
              { [DbPaths.ENTRY_GRAM_INFO_ABBREV_VALUE]: abbreviation },
              { [DbPaths.ENTRY_SENSES_PART_OF_SPEECH_VALUE]: abbreviation },
              { [DbPaths.ENTRY_SUBENTRIES_PART_OF_SPEECH_VALUE]: abbreviation },
            ],
          })
        : 0;
    }),
  );

  // send only those that are used in entries
  dbItem.partsOfSpeech = dbItem.partsOfSpeech
    .map((part, index) => {
      return { ...part, entriesCount: partsOfSpeechCounts[index] };
    })
    .filter((part) => part.entriesCount);

  // get reversal entry counts
  if (!dbItem.reversalLanguages) {
    dbItem.reversalLanguages = []; // FLex does not include this when there are no reversal langs
  }
  const reversalEntriesCounts = await Promise.all(
    dbItem.reversalLanguages.map(async ({ lang }) => {
      return db.collection(DB_COLLECTION_REVERSALS).countDocuments({
        dictionaryId,
        [DbPaths.ENTRY_REVERSAL_FORM_LANG]: lang,
      });
    }),
  );
  reversalEntriesCounts.forEach((entriesCount, index) => {
    dbItem.reversalLanguages[index].entriesCount = entriesCount;
  });

  // eslint-disable-next-line no-console
  console.log(`Found ${dictionaryId}`, dbItem);

  return Response.success(dbItem);
}

export default handler;
