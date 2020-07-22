import { APIGatewayEvent, Context, Callback } from 'aws-lambda';
import { MongoClient } from 'mongodb';
import { connectToDB } from './mongo';
import {
  DB_NAME,
  DB_COLLECTION_DICTIONARY_ENTRIES,
  DB_MAX_DOCUMENTS_PER_CALL,
  DB_COLLATION_LOCALE_DEFAULT_FOR_INSENSITIVITY,
  DB_COLLATION_STRENGTH_FOR_CASE_INSENSITIVITY,
  DB_COLLATION_STRENGTH_FOR_SENSITIVITY,
  DB_COLLATION_STRENGTH_FOR_INSENSITIVITY,
  DB_COLLATION_LOCALES,
} from './db';
import { DbFindParameters } from './base.model';
import { DbPaths } from './entry.model';
import { getDbSkip } from './utils';

import * as Response from './response';

let dbClient: MongoClient;

export async function handler(
  event: APIGatewayEvent,
  context: Context,
  callback: Callback,
): Promise<void> {
  // eslint-disable-next-line no-param-reassign
  context.callbackWaitsForEmptyEventLoop = false;

  try {
    dbClient = await connectToDB();
    const db = dbClient.db(DB_NAME);

    const dictionaryId = event.pathParameters?.dictionaryId;
    const text = event.queryStringParameters?.text;
    const mainLang = event.queryStringParameters?.mainLang; // main language of the dictionary
    const lang = event.queryStringParameters?.lang; // this is used to limit which language to search

    const partOfSpeech = event.queryStringParameters?.partOfSpeech;
    const matchPartial = event.queryStringParameters?.matchPartial;
    const matchAccents = event.queryStringParameters?.matchAccents; // NOTE: matching accent works only for fulltext searching

    const semDomAbbrev = event.queryStringParameters?.semDomAbbrev;
    const searchSemDoms = event.queryStringParameters?.searchSemDoms;

    const countTotalOnly = event.queryStringParameters?.countTotalOnly;

    const pageNumber = Math.max(Number(event.queryStringParameters?.pageNumber ?? '1'), 1);
    const pageLimit = Math.min(
      Math.max(Number(event.queryStringParameters?.pageLimit ?? DB_MAX_DOCUMENTS_PER_CALL), 1),
      DB_MAX_DOCUMENTS_PER_CALL,
    );

    if (!text) {
      return callback(null, Response.badRequest('Search text must be specified.'));
    }

    // set up main search
    let entries;
    let locale = DB_COLLATION_LOCALE_DEFAULT_FOR_INSENSITIVITY;
    let strength = DB_COLLATION_STRENGTH_FOR_CASE_INSENSITIVITY;
    const dbSkip = getDbSkip(pageNumber, pageLimit);
    const primaryFilter: DbFindParameters = { dictionaryId };

    // Semantic domains search
    if (searchSemDoms === '1') {
      let dbFind;
      if (semDomAbbrev && semDomAbbrev !== '') {
        const abbreviationRegex = { $in: [semDomAbbrev, new RegExp(`^${semDomAbbrev}.`)] };
        if (lang) {
          if (DB_COLLATION_LOCALES.includes(lang)) {
            locale = lang;
          }

          dbFind = {
            ...primaryFilter,
            [DbPaths.ENTRY_SEM_DOMS_ABBREV]: {
              $elemMatch: {
                lang,
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
        dbFind = { ...primaryFilter, [DbPaths.ENTRY_SEM_DOMS_NAME_VALUE]: text };
      }

      if (countTotalOnly === '1') {
        const count = await db.collection(DB_COLLECTION_DICTIONARY_ENTRIES).countDocuments(dbFind);
        return callback(null, Response.success({ count }));
      }

      entries = await db
        .collection(DB_COLLECTION_DICTIONARY_ENTRIES)
        .find(dbFind)
        .skip(dbSkip)
        .limit(pageLimit)
        .toArray();

      return callback(null, Response.success(entries));
    }

    let langFilter: DbFindParameters;
    const regexFilter: DbFindParameters = { $regex: text, $options: 'i' };

    if (partOfSpeech) {
      primaryFilter[DbPaths.ENTRY_PART_OF_SPEECH_VALUE] = partOfSpeech;
    }

    if (lang) {
      if (DB_COLLATION_LOCALES.includes(lang)) {
        locale = lang;
      }

      let langFieldToFilter: string;
      if (mainLang && mainLang === lang) {
        langFieldToFilter = 'mainHeadWord';
      } else {
        langFieldToFilter = 'senses.definitionOrGloss';
      }

      langFilter = {
        [langFieldToFilter]: {
          $elemMatch: {
            lang,
            value: regexFilter,
          },
        },
      };
    } else {
      langFilter = {
        $or: [
          { [DbPaths.ENTRY_MAIN_HEADWORD_VALUE]: regexFilter },
          { [DbPaths.ENTRY_DEFINITION_VALUE]: regexFilter },
        ],
      };
    }

    if (matchPartial === '1') {
      const dictionaryPartialSearch = {
        $and: [primaryFilter, langFilter],
      };

      if (matchAccents === '1') {
        strength = DB_COLLATION_STRENGTH_FOR_SENSITIVITY;
      }

      console.log(
        `Searching ${dictionaryId} using partial match and locale ${locale} and strength ${strength} ${JSON.stringify(
          dictionaryPartialSearch,
        )}`,
      );

      if (countTotalOnly === '1') {
        // TODO: countDocuments might not be 100%, but should be more than the actual count, so it would page to the end
        const count = await db
          .collection(DB_COLLECTION_DICTIONARY_ENTRIES)
          .countDocuments(dictionaryPartialSearch);

        return callback(null, Response.success({ count }));
      }

      entries = await db
        .collection(DB_COLLECTION_DICTIONARY_ENTRIES)
        .find(dictionaryPartialSearch)
        .collation({ locale, strength })
        .skip(dbSkip)
        .limit(pageLimit)
        .toArray();
    } else {
      // NOTE: Mongo text search can do language specific stemming,
      // but then each search much specify correct language in a field named "language".
      // To use this, we will need to distinguish between lang field in Entry, and a special language field
      // that is one of the valid Mongo values, or "none".
      // By setting $language: "none" in all queries and in index, we are skipping language-specific stemming.
      // If we wanted to use language stemming, then we must specify language in each search,
      // and UNION all searches if language-independent search is desired
      const $language = event.queryStringParameters?.stemmingLanguage ?? 'none';
      const $diacriticSensitive = matchAccents === '1';
      const $text = { $search: `"${text}"`, $language, $diacriticSensitive };
      const dictionaryFulltextSearch = { ...primaryFilter, $text };
      if (lang) {
        const dbFind = [{ $match: dictionaryFulltextSearch }, { $match: langFilter }];

        console.log(`Searching ${dictionaryId} using fulltext ${JSON.stringify(dbFind)}`);

        if (countTotalOnly === '1') {
          /* TODO: There might be a way to count docs in aggregation, but I have not figured it out yet...
          const count = await db.collection(DB_COLLECTION_ENTRIES).countDocuments(dbFind);
          */
          entries = await db
            .collection(DB_COLLECTION_DICTIONARY_ENTRIES)
            .aggregate(dbFind)
            .toArray();
          const count = entries.length;

          return callback(null, Response.success({ count }));
        }

        entries = await db
          .collection(DB_COLLECTION_DICTIONARY_ENTRIES)
          .aggregate(dbFind)
          .skip(dbSkip)
          .limit(pageLimit)
          .toArray();
      } else {
        console.log(`Searching ${dictionaryId} using ${JSON.stringify(dictionaryFulltextSearch)}`);

        if (countTotalOnly === '1') {
          const count = await db
            .collection(DB_COLLECTION_DICTIONARY_ENTRIES)
            .countDocuments(dictionaryFulltextSearch);

          return callback(null, Response.success({ count }));
        }

        entries = await db
          .collection(DB_COLLECTION_DICTIONARY_ENTRIES)
          .find(dictionaryFulltextSearch)
          .skip(dbSkip)
          .limit(pageLimit)
          .toArray();
      }
    }

    if (!entries.length) {
      return callback(null, Response.notFound([{}]));
    }
    return callback(null, Response.success(entries));
  } catch (error) {
    // eslint-disable-next-line no-console
    console.log(error);
    return callback(null, Response.failure({ errorType: error.name, errorMessage: error.message }));
  }
}

export default handler;
