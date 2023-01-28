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

import { APIGatewayEvent, APIGatewayProxyResult } from 'aws-lambda';
import { MongoClient } from 'mongodb';
import { connectToDB } from './mongo';
import {
  MONGO_DB_NAME,
  DB_MAX_DOCUMENTS_PER_CALL,
  DB_COLLECTION_ENTRIES,
  DB_COLLECTION_REVERSALS,
  DB_COLLATION_LOCALE_DEFAULT_FOR_INSENSITIVITY,
  DbCollationStrength,
  DB_COLLATION_LOCALES,
  dbCollectionEntries,
} from './db';
import { DbFindParameters } from './base.model';
import { DictionaryEntry, DbPaths, ENTRY_TYPE_REVERSAL } from './entry.model';
import { getDbSkip } from './utils';
import * as Response from './response';

let dbClient: MongoClient;

export async function handler(event: APIGatewayEvent): Promise<APIGatewayProxyResult> {
  const dictionaryId = event.pathParameters?.dictionaryId?.toLowerCase();
  if (!dictionaryId) {
    return Response.badRequest('Dictionary must be in the path.');
  }

  const text = event.queryStringParameters?.text ?? '';
  if (!text) {
    return Response.badRequest('Browse head letter must be specified.');
  }

  const mainLang = event.queryStringParameters?.mainLang; // main language of the dictionary
  const lang = event.queryStringParameters?.lang ?? ''; // this is used to limit which language to search
  const isReversal = event.queryStringParameters?.entryType === ENTRY_TYPE_REVERSAL;

  const countTotalOnly = event.queryStringParameters?.countTotalOnly;

  const pageNumber = Math.max(Number(event.queryStringParameters?.pageNumber ?? '1'), 1);
  const pageLimit = Math.min(
    Math.max(Number(event.queryStringParameters?.pageLimit ?? DB_MAX_DOCUMENTS_PER_CALL), 1),
    DB_MAX_DOCUMENTS_PER_CALL,
  );

  const dbCollection = isReversal ? DB_COLLECTION_REVERSALS : dbCollectionEntries(dictionaryId);
  let dbLocale = DB_COLLATION_LOCALE_DEFAULT_FOR_INSENSITIVITY;
  let entries: Document[];
  let primarySearch = true;
  const dbFind: DbFindParameters = {};
  const dbSkip = getDbSkip(pageNumber, pageLimit);

  if (isReversal) {
    if (lang === '') {
      return Response.badRequest('Language must be specified for browsing reversal entries.');
    }

    dbFind[DbPaths.DICTIONARY_ID] = dictionaryId;
    dbFind[DbPaths.ENTRY_REVERSAL_FORM_LANG] = lang;
    if (DB_COLLATION_LOCALES.includes(lang)) {
      dbLocale = lang;
    }
  } else if (lang === '') {
    if (mainLang && DB_COLLATION_LOCALES.includes(mainLang)) {
      dbLocale = mainLang;
    }
  } else {
    // generate reversal entries based on searching via definitions
    primarySearch = false;
  }

  dbClient = await connectToDB();
  const db = dbClient.db(MONGO_DB_NAME);

  if (primarySearch) {
    dbFind[DbPaths.LETTER_HEAD] = text;

    if (countTotalOnly === '1') {
      const count = await db.collection(dbCollection).countDocuments(dbFind);
      return Response.success({ count });
    }

    entries = await db
      .collection(dbCollection)
      .find(dbFind)
      .collation({ locale: dbLocale, strength: DbCollationStrength.CASE_INSENSITIVITY })
      .sort({ [DbPaths.SORT_INDEX]: 1 })
      .skip(dbSkip)
      .limit(pageLimit)
      .toArray();
  } else {
    dbFind.reversalLetterHeads = { lang, value: text };

    const pipeline: object[] = [
      { $match: dbFind },
      { $unwind: `$${DbPaths.ENTRY_SENSES}` },
      { $unwind: `$${DbPaths.ENTRY_DEFINITION_OR_GLOSS}` },
      { $match: { [DbPaths.ENTRY_DEFINITION_OR_GLOSS_LANG]: lang } },
    ];

    if (countTotalOnly && countTotalOnly === '1') {
      pipeline.push({ $count: 'count' });
      const count =
        (await db.collection(dbCollectionEntries(dictionaryId)).aggregate(pipeline).next()) ?? '0';
      return Response.success(count);
    }

    pipeline.push({ $sort: { [DbPaths.ENTRY_DEFINITION_OR_GLOSS_VALUE]: 1 } });
    entries = await db
      .collection<DictionaryEntry>(DB_COLLECTION_ENTRIES)
      .aggregate(pipeline, {
        collation: { locale: dbLocale, strength: DbCollationStrength.CASE_INSENSITIVITY },
      })
      .skip(dbSkip)
      .limit(pageLimit)
      .toArray();
  }

  if (!entries.length) {
    return Response.notFound();
  }

  return Response.success(entries);
}

export default handler;
