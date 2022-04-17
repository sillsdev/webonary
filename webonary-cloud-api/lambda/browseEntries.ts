/**
 * @api {get} /browse/entry/:dictionaryId Get dictionary entries for a letter head.
 * @apiName BrowseDictionaryEntries
 * @apiDescription Gets dictionary or reversal entries that match the specified letter head. Returns ReversalEntryItem's
 *  if entryType == 'reversalindexentry' else DictionaryEntryItem's.
 * (https://github.com/sillsdev/webonary/blob/develop/webonary-cloud-api/lambda/entry.model.ts)
 * @apiGroup Dictionary
 * @apiUse DictionaryIdPath
 * @apiParam {String} text Letter head to browse.
 * @apiParam {String} [mainLang] Main language of the dictionary, used for setting the db locale.
 * @apiParam {String} [lang] Language to search through. This must be specified for browsing reversal entries.
 * @apiParam {String=entry,reversalindexentry} [entryType] Type of the entry to get: 'entry' for main entry and
 * 'reversalindexentry' for reversal entry. Defaults to 'entry'.
 * @apiParam {Number=0,1} [countTotalOnly] 1 to return only the count, and 0 otherwise. Defaults to 0.
 * @apiParam {Number} [pageNumber] 1-indexed page number for the results. Defaults to 1.
 * @apiParam {Number} [pageLimit] Number of entries per page. Max is 100. Defaults to 100.
 *
 * @apiError (404) NotFound There are no matching entries.
 */

import { APIGatewayEvent, Context, Callback } from 'aws-lambda';
import { MongoClient } from 'mongodb';
import { connectToDB } from './mongo';
import {
  DB_NAME,
  DB_MAX_DOCUMENTS_PER_CALL,
  DB_COLLECTION_DICTIONARY_ENTRIES,
  DB_COLLECTION_REVERSAL_ENTRIES,
  DB_COLLATION_LOCALE_DEFAULT_FOR_INSENSITIVITY,
  DB_COLLATION_STRENGTH_FOR_CASE_INSENSITIVITY,
  DB_COLLATION_LOCALES,
} from './db';
import { DbFindParameters } from './base.model';
import { DictionaryEntry, ReversalEntry, DbPaths, ENTRY_TYPE_REVERSAL } from './entry.model';
import { getDbSkip } from './utils';
import * as Response from './response';

let dbClient: MongoClient;

export async function handler(
  event: APIGatewayEvent,
  context: Context,
  callback: Callback,
): Promise<void> {
  try {
    // eslint-disable-next-line no-param-reassign
    context.callbackWaitsForEmptyEventLoop = false;

    const dictionaryId = event.pathParameters?.dictionaryId;
    const text = event.queryStringParameters?.text ?? '';
    const mainLang = event.queryStringParameters?.mainLang; // main language of the dictionary
    const lang = event.queryStringParameters?.lang ?? ''; // this is used to limit which language to search
    const isReversalEntry = event.queryStringParameters?.entryType === ENTRY_TYPE_REVERSAL;

    const countTotalOnly = event.queryStringParameters?.countTotalOnly;

    const pageNumber = Math.max(Number(event.queryStringParameters?.pageNumber ?? '1'), 1);
    const pageLimit = Math.min(
      Math.max(Number(event.queryStringParameters?.pageLimit ?? DB_MAX_DOCUMENTS_PER_CALL), 1),
      DB_MAX_DOCUMENTS_PER_CALL,
    );

    if (text === '') {
      return callback(null, Response.badRequest('Browse letter head must be specified.'));
    }

    let dbCollection = '';
    let dbSort = {};
    let dbLocale = DB_COLLATION_LOCALE_DEFAULT_FOR_INSENSITIVITY;
    let entries: DictionaryEntry[] | ReversalEntry[];
    let primarySearch = true;
    const dbFind: DbFindParameters = { dictionaryId };
    const dbSkip = getDbSkip(pageNumber, pageLimit);

    if (isReversalEntry) {
      if (lang === '') {
        return callback(
          null,
          Response.badRequest('Language must be specified for browsing reversal entries.'),
        );
      }

      primarySearch = true;
      dbCollection = DB_COLLECTION_REVERSAL_ENTRIES;
      dbFind[DbPaths.ENTRY_REVERSAL_FORM_LANG] = lang;
      if (DB_COLLATION_LOCALES.includes(lang)) {
        dbLocale = lang;
      }

      // TODO: Make sure to set default sort for entries to be on main headword browse letter and value
      dbSort = { 'reversalForm.0.value': 1, 'reversalForm.1.value': 1 };
    } else {
      dbCollection = DB_COLLECTION_DICTIONARY_ENTRIES;
      if (lang === '') {
        if (mainLang && DB_COLLATION_LOCALES.includes(mainLang)) {
          dbLocale = mainLang;
        }

        // TODO: Make sure to set default sort for entries to be on main headword browse letter and value
        dbSort = { 'mainHeadWord.0.value': 1, 'mainHeadWord.1.value': 1 };
      } else {
        // generate reversal entries based on searching via definitions
        primarySearch = false;

        // TODO: Include reversal language in sorting?
        /*
        dbSortKey = DbPaths.ENTRY_MAIN_HEADWORD_VALUE;
        if (DB_COLLATION_LOCALES.includes(lang)) {
          dbLocale = lang;
        }
        */
      }
    }

    dbClient = await connectToDB();
    const db = dbClient.db(DB_NAME);

    if (primarySearch) {
      dbFind.letterHead = text;

      if (countTotalOnly && countTotalOnly === '1') {
        const count = await db.collection(dbCollection).countDocuments(dbFind);
        return callback(null, Response.success({ count }));
      }

      entries = await db
        .collection(dbCollection)
        .find(dbFind)
        .collation({ locale: dbLocale, strength: DB_COLLATION_STRENGTH_FOR_CASE_INSENSITIVITY })
        .sort({ sortIndex: 1, ...dbSort })
        .skip(dbSkip)
        .limit(pageLimit)
        .toArray();
    } else {
      dbFind.reversalLetterHeads = { lang, value: text };

      const pipeline: object[] = [
        { $match: dbFind },
        { $unwind: `$${DbPaths.ENTRY_SENSES}` },
        { $unwind: `$${DbPaths.ENTRY_DEFINITION}` },
        { $match: { [DbPaths.ENTRY_DEFINITION_LANG]: lang } },
      ];

      if (countTotalOnly && countTotalOnly === '1') {
        pipeline.push({ $count: 'count' });
        const count = await db
          .collection(DB_COLLECTION_DICTIONARY_ENTRIES)
          .aggregate(pipeline)
          .next();
        return callback(null, Response.success(count));
      }

      pipeline.push({ $sort: { [DbPaths.ENTRY_DEFINITION_VALUE]: 1 } });
      entries = await db
        .collection(DB_COLLECTION_DICTIONARY_ENTRIES)
        .aggregate(pipeline, {
          collation: { locale: dbLocale, strength: DB_COLLATION_STRENGTH_FOR_CASE_INSENSITIVITY },
        })
        .skip(dbSkip)
        .limit(pageLimit)
        .toArray();
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
