/**
 * @api {get} /browse/entry/:dictionaryId Get dictionary entries for a letter head.
 * @apiName BrowseDictionaryEntries
 * @apiDescription Gets dictionary or reversal entries that match the specified letter head. Returns ReversalEntryItem's
 *  if entryType == 'reversalindexentry' else DictionaryEntryItem's.
 * (https://github.com/sillsdev/webonary/blob/develop/webonary-cloud-api/lambda/entry.model.ts)
 * @apiGroup Dictionary
 * @apiUse DictionaryIdPath
 * @apiParam {String} text Letter head to browse.
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
  DB_COLLECTION_REVERSALS,
  dbCollectionEntries,
} from './db';
import { DbFindParameters } from './base.model';
import { DbPaths, ENTRY_TYPE_REVERSAL } from './entry.model';
import { escapeStringRegexp, getDbSkip } from './utils';
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

  const lang = event.queryStringParameters?.lang ?? ''; // this is used to limit which language to search
  const isReversal = event.queryStringParameters?.entryType === ENTRY_TYPE_REVERSAL;

  const countTotalOnly = event.queryStringParameters?.countTotalOnly;

  const pageNumber = Math.max(Number(event.queryStringParameters?.pageNumber ?? '1'), 1);
  const pageLimit = Math.min(
    Math.max(Number(event.queryStringParameters?.pageLimit ?? DB_MAX_DOCUMENTS_PER_CALL), 1),
    DB_MAX_DOCUMENTS_PER_CALL,
  );

  let dbCollection;
  const dbFind: DbFindParameters = {};

  if (isReversal) {
    if (lang === '') {
      return Response.badRequest('Language must be specified for browsing reversal entries.');
    }

    dbCollection = DB_COLLECTION_REVERSALS;
    dbFind[DbPaths.DICTIONARY_ID] = dictionaryId;
    dbFind[DbPaths.ENTRY_REVERSAL_FORM_LANG] = lang;
  } else {
    dbCollection = dbCollectionEntries(dictionaryId);
  }

  dbFind[DbPaths.LETTER_HEAD] = { $regex: new RegExp(`^${escapeStringRegexp(text)}$`, 'i') };

  // eslint-disable-next-line no-console
  console.log(`Browsing ${dbCollection} using ${JSON.stringify(dbFind)}`);

  dbClient = await connectToDB();
  const db = dbClient.db(MONGO_DB_NAME);

  if (countTotalOnly === '1') {
    const count = await db.collection(dbCollection).countDocuments(dbFind);

    // eslint-disable-next-line no-console
    console.log(`Found count ${count}`);
    return Response.success({ count });
  }

  const entries = await db
    .collection(dbCollection)
    .find(dbFind)
    .sort({ [DbPaths.SORT_INDEX]: 1 })
    .skip(getDbSkip(pageNumber, pageLimit))
    .limit(pageLimit)
    .toArray();

  if (!entries.length) {
    return Response.notFound();
  }

  // eslint-disable-next-line no-console
  console.log(`Found first entry ${entries[0]}`);

  return Response.success(entries);
}

export default handler;
