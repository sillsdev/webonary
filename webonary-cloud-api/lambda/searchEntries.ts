/**
 * @api {get} /search/entry/:dictionaryId Search dictionary entries
 * @apiName SearchDictionaryEntries
 * @apiDescription Searches the dictionary for entries that match. Returns an array of DictionaryEntryItem's.
 * (https://github.com/sillsdev/webonary/blob/develop/webonary-cloud-api/lambda/entry.model.ts)
 * @apiGroup Dictionary
 * @apiUse DictionaryIdPath
 * @apiParam {String} text
 * @apiParam {String} [mainLang] Main language of the dictionary, used for setting the db locale.
 * @apiParam {String} [lang] Language to search through.
 * @apiParam {String} [partofspeech] Filter results by part of speech.
 * @apiParam {Number=0,1} [matchPartial] 1 to allow partial matches, and 0 otherwise. Defaults to 0.
 * @apiParam {Number=0,1} [matchAccents] 1 to match accents, and 0 otherwise. Defaults to 0.
 * @apiParam {String} [semDomAbbrev] Filter by semantic domain abbreviation.
 * @apiParam {String} [searchSemDoms] 1 to search by semantic domains, and 0 otherwise. Defaults to 0.
 * @apiParam {Number=0,1} [countTotalOnly] 1 to return only the count, and 0 otherwise. Defaults to 0.
 * @apiParam {Number} [pageNumber] 1-indexed page number for the results. Defaults to 1.
 * @apiParam {Number} [pageLimit] Number of entries per page. Max is 100. Defaults to 100.
 *
 * @apiError (404) NotFound There are no matching entries.
 */

import { APIGatewayEvent, APIGatewayProxyResult } from 'aws-lambda';
import { MongoClient } from 'mongodb';
import { connectToDB } from './mongo';
import {
  MONGO_DB_NAME,
  DB_COLLECTION_DICTIONARY_ENTRIES,
  DB_MAX_DOCUMENTS_PER_CALL,
  DB_COLLATION_LOCALE_DEFAULT_FOR_INSENSITIVITY,
  DbCollationStrength,
  DB_COLLATION_LOCALES,
} from './db';
import { DbFindParameters } from './base.model';
import { DbPaths } from './entry.model';
import { getDbSkip } from './utils';

import * as Response from './response';

export interface SearchEntriesArguments {
  pageNumber: number;
  pageLimit: number;
  dictionaryId: string | undefined;
  searchSemDoms: boolean;
  semDomAbbrev: string | undefined;
  lang: string | undefined;
  text: string;
  countTotalOnly: boolean;
  // Filter to the specified parts of speech.
  // If this is undefined or an empty array, then no filter is applied.
  partOfSpeech: string[] | undefined;
  mainLang: string | undefined;
  matchPartial: boolean;
  matchAccents: boolean;
  $language: string;
}

export async function searchEntries(args: SearchEntriesArguments): Promise<APIGatewayProxyResult> {
  const dbClient: MongoClient = await connectToDB();
  const db = dbClient.db(MONGO_DB_NAME);

  // set up main search
  let entries;
  let locale = DB_COLLATION_LOCALE_DEFAULT_FOR_INSENSITIVITY;
  let strength = DbCollationStrength.CASE_INSENSITIVITY;
  const dbSkip = getDbSkip(args.pageNumber, args.pageLimit);
  let primaryFilter: DbFindParameters = { dictionaryId: args.dictionaryId };

  // Semantic domains search
  if (args.searchSemDoms) {
    let dbFind;
    if (args.semDomAbbrev && args.semDomAbbrev !== '') {
      const abbreviationRegex = {
        $in: [args.semDomAbbrev, new RegExp(`^${args.semDomAbbrev}.`)],
      };
      if (args.lang) {
        dbFind = {
          ...primaryFilter,
          [DbPaths.ENTRY_SEM_DOMS_ABBREV]: {
            $elemMatch: {
              lang: args.lang,
              value: abbreviationRegex,
            },
          },
        };
      } else {
        dbFind = {
          ...primaryFilter,
          [DbPaths.ENTRY_SEM_DOMS_ABBREV_VALUE]: abbreviationRegex,
        };
      }
    } else {
      dbFind = { ...primaryFilter, [DbPaths.ENTRY_SEM_DOMS_NAME_VALUE]: args.text };
    }

    if (args.countTotalOnly) {
      const count = await db.collection(DB_COLLECTION_DICTIONARY_ENTRIES).countDocuments(dbFind);
      return Response.success({ count });
    }

    entries = await db
      .collection(DB_COLLECTION_DICTIONARY_ENTRIES)
      .find(dbFind)
      .skip(dbSkip)
      .limit(args.pageLimit)
      .toArray();

    return Response.success(entries);
  }

  if (args.partOfSpeech && args.partOfSpeech.length > 0) {
    primaryFilter[DbPaths.ENTRY_PART_OF_SPEECH_VALUE] = {
      $in: args.partOfSpeech,
    };
  }

  if (args.lang) {
    if (DB_COLLATION_LOCALES.includes(args.lang)) {
      locale = args.lang;
    }

    primaryFilter = {
      $and: [
        primaryFilter,
        {
          $or: [
            { 'mainheadword.lang': args.lang },
            { 'senses.definitionorgloss.lang': args.lang },
            { 'reversalLetterHeads.lang': args.lang },
            { 'pronunciations.lang': args.lang },
            { 'morphosyntaxanalysis.partofspeech.lang': args.lang },
          ],
        },
      ],
    };
  }

  if (args.matchPartial) {
    const dictionaryPartialSearch = {
      ...primaryFilter,
      [DbPaths.ENTRY_LANG_TEXTS]: { $regex: args.text, $options: 'i' },
    };

    if (args.matchAccents) {
      strength = DbCollationStrength.SENSITIVITY;
    }

    // eslint-disable-next-line no-console
    console.log(
      `Searching ${
        args.dictionaryId
      } using partial match and locale ${locale} and strength ${strength} ${JSON.stringify(
        dictionaryPartialSearch,
      )}`,
    );

    if (args.countTotalOnly) {
      // TODO: countDocuments might not be 100%, but should be more than the actual count, so it would page to the end
      const count = await db
        .collection(DB_COLLECTION_DICTIONARY_ENTRIES)
        .countDocuments(dictionaryPartialSearch);

      return Response.success({ count });
    }

    entries = await db
      .collection(DB_COLLECTION_DICTIONARY_ENTRIES)
      .find(dictionaryPartialSearch)
      .collation({ locale, strength })
      .sort({ 'mainheadword.value': 1, _id: 1 })
      .skip(dbSkip)
      .limit(args.pageLimit)
      .toArray();
  } else {
    // NOTE: Mongo text search can do language specific stemming,
    // but then each search much specify correct language in a field named "language".
    // To use this, we will need to distinguish between lang field in Entry, and a special language field
    // that is one of the valid Mongo values, or "none".
    // By setting $language: "none" in all queries and in index, we are skipping language-specific stemming.
    // If we wanted to use language stemming, then we must specify language in each search,
    // and UNION all searches if language-independent search is desired
    const $diacriticSensitive = args.matchAccents;
    const $text = { $search: `"${args.text}"`, $language: args.$language, $diacriticSensitive };
    const dictionaryFulltextSearch = { ...primaryFilter, $text };

    // eslint-disable-next-line no-console
    console.log(`Searching ${args.dictionaryId} using ${JSON.stringify(dictionaryFulltextSearch)}`);

    if (args.countTotalOnly) {
      const count = await db
        .collection(DB_COLLECTION_DICTIONARY_ENTRIES)
        .countDocuments(dictionaryFulltextSearch);

      return Response.success({ count });
    }

    entries = await db
      .collection(DB_COLLECTION_DICTIONARY_ENTRIES)
      .find(dictionaryFulltextSearch)
      .sort({ 'mainheadword.value': 1, _id: 1 })
      .skip(dbSkip)
      .limit(args.pageLimit)
      .toArray();
  }

  if (!entries.length) {
    return Response.notFound();
  }
  return Response.success(entries);
}

export async function handler(event: APIGatewayEvent): Promise<APIGatewayProxyResult> {
  const dictionaryId = event.pathParameters?.dictionaryId?.toLowerCase();
  const text = event.queryStringParameters?.text;
  const mainLang = event.queryStringParameters?.mainLang; // main language of the dictionary
  const lang = event.queryStringParameters?.lang; // this is used to limit which language to search

  const partOfSpeech = event.multiValueQueryStringParameters?.partOfSpeech;
  const matchPartial = event.queryStringParameters?.matchPartial === '1';
  const matchAccents = event.queryStringParameters?.matchAccents === '1'; // NOTE: matching accent works only for fulltext searching

  const semDomAbbrev = event.queryStringParameters?.semDomAbbrev;
  const searchSemDoms = event.queryStringParameters?.searchSemDoms === '1';

  const countTotalOnly = event.queryStringParameters?.countTotalOnly === '1';
  const $language = event.queryStringParameters?.stemmingLanguage ?? 'none';

  const pageNumber = Math.max(Number(event.queryStringParameters?.pageNumber ?? '1'), 1);
  const pageLimit = Math.min(
    Math.max(Number(event.queryStringParameters?.pageLimit ?? DB_MAX_DOCUMENTS_PER_CALL), 1),
    DB_MAX_DOCUMENTS_PER_CALL,
  );

  if (!text) {
    return Response.badRequest('Search text must be specified.');
  }
  const response = await searchEntries({
    pageNumber,
    pageLimit,
    dictionaryId,
    searchSemDoms,
    semDomAbbrev,
    lang,
    text,
    countTotalOnly,
    partOfSpeech,
    mainLang,
    matchPartial,
    matchAccents,
    $language,
  });

  return response;
}

export default handler;
