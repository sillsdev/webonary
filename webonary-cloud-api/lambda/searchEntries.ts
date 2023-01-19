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
 * @apiParam {String} [semanticDomain] Filter results by semantic domain hierarchical abbreviation (e.g. 1.1)
 * @apiParam {Number=0,1} [matchPartial] 1 to allow partial matches, and 0 otherwise. Defaults to 0.
 * @apiParam {Number=0,1} [matchAccents] 1 to match accents, and 0 otherwise. Defaults to 0.
 * @apiParam {String} [searchSemDoms] 1 to search by semantic domains, and 0 otherwise. Defaults to 0.
 * @apiParam {String} [semDomAbbrev] Filter by semantic domain abbreviation.
 * @apiParam {Number=0,1} [countTotalOnly] 1 to return only the count, and 0 otherwise. Defaults to 0.
 * @apiParam {Number} [pageNumber] 1-indexed page number for the results. Defaults to 1.
 * @apiParam {Number} [pageLimit] Number of entries per page. Max is 100. Defaults to 100.
 *
 * @apiError (404) NotFound There are no matching entries.
 */

import { APIGatewayEvent, APIGatewayProxyResult } from 'aws-lambda';
import { MongoClient } from 'mongodb';

import { DbFindParameters } from './base.model';
import { DbPaths } from './entry.model';
import {
  MONGO_DB_NAME,
  DB_MAX_DOCUMENTS_PER_CALL,
  DB_COLLATION_LOCALE_DEFAULT_FOR_INSENSITIVITY,
  DbCollationStrength,
  DB_COLLATION_LOCALES,
  dbCollectionEntries,
} from './db';
import { connectToDB } from './mongo';
import * as Response from './response';
import {
  escapeStringRegexp,
  getDbSkip,
  removeDiacritics,
  semanticDomainAbbrevRegex,
} from './utils';

export async function handler(event: APIGatewayEvent): Promise<APIGatewayProxyResult> {
  const dictionaryId = event.pathParameters?.dictionaryId?.toLowerCase();
  if (!dictionaryId) {
    return Response.badRequest('Dictionary must be in the path.');
  }

  let text = event.queryStringParameters?.text?.trim();
  if (!text) {
    return Response.badRequest('Search text must be specified.');
  }
  text = decodeURIComponent(text.replace(/\+/g, ' '));

  // `lang` is used to limit which language to search. Webonary will always send this.
  const lang = event.queryStringParameters?.lang;

  // These are used to show all entries for a semantic domain
  const semDomAbbrev = event.queryStringParameters?.semDomAbbrev;
  const searchSemDoms = event.queryStringParameters?.searchSemDoms === '1';

  // These parameters are used to filter search results
  const partOfSpeech = event.multiValueQueryStringParameters?.partOfSpeech;
  const semanticDomain = event.queryStringParameters?.semanticDomain;
  const matchPartial = event.queryStringParameters?.matchPartial === '1';
  const matchAccents = event.queryStringParameters?.matchAccents === '1';

  const countTotalOnly = event.queryStringParameters?.countTotalOnly === '1';

  // This is not used by Webonary yet, so always defaults to 'none'
  const $language = event.queryStringParameters?.stemmingLanguage ?? 'none';

  const pageNumber = Math.max(Number(event.queryStringParameters?.pageNumber ?? '1'), 1);
  const pageLimit = Math.min(
    Math.max(Number(event.queryStringParameters?.pageLimit ?? DB_MAX_DOCUMENTS_PER_CALL), 1),
    DB_MAX_DOCUMENTS_PER_CALL,
  );

  const dbClient: MongoClient = await connectToDB();
  const dbCollection = dbClient.db(MONGO_DB_NAME).collection(dbCollectionEntries(dictionaryId));

  // STEP 1: Set main filters
  let dbFind: DbFindParameters = {};
  if (partOfSpeech && partOfSpeech.length > 0) {
    // decode since spaces can exist in part of speech
    const partOfSpeechDecoded = partOfSpeech.map((part) =>
      decodeURIComponent(part.replace(/\+/g, ' ')),
    );

    // part of speech can be in several different fields
    dbFind = {
      $or: [
        {
          [DbPaths.ENTRY_PART_OF_SPEECH_VALUE]: { $in: partOfSpeechDecoded },
        },
        {
          [DbPaths.ENTRY_GRAM_INFO_ABBREV_VALUE]: { $in: partOfSpeechDecoded },
        },
      ],
    };
  }

  if (semanticDomain) {
    dbFind[DbPaths.ENTRY_SEM_DOMS_ABBREV_VALUE] = semanticDomainAbbrevRegex(semanticDomain);
  }

  // STEP 2: Set language condition
  let langTextsPath;
  if (matchAccents) {
    langTextsPath = DbPaths.ENTRY_LANG_TEXTS;
  } else {
    langTextsPath = DbPaths.ENTRY_LANG_UNACCENTED_TEXTS;
    text = removeDiacritics(text);
  }

  // STEP 3: Conduct main search (semantic domain, partial search, or fulltext search)
  let cursor;
  if (searchSemDoms) {
    if (semDomAbbrev && semDomAbbrev !== '') {
      dbFind[DbPaths.ENTRY_SEM_DOMS_ABBREV_VALUE] = semanticDomainAbbrevRegex(semDomAbbrev);
    } else {
      dbFind[DbPaths.ENTRY_SEM_DOMS_NAME_VALUE] = text;
    }

    cursor = dbCollection.find(dbFind);
  } else if (matchPartial) {
    const locale =
      lang && DB_COLLATION_LOCALES.includes(lang)
        ? lang
        : DB_COLLATION_LOCALE_DEFAULT_FOR_INSENSITIVITY;

    const strength = matchAccents
      ? DbCollationStrength.CASE_INSENSITIVITY
      : DbCollationStrength.INSENSITIVITY;

    const searchPath = lang ? `${langTextsPath}.${lang}` : DbPaths.ENTRY_SEARCH_TEXTS;
    dbFind[searchPath] = { $regex: escapeStringRegexp(text), $options: 'i' };

    cursor = dbCollection
      .find(dbFind)
      .collation({ locale, strength })
      .sort({ [DbPaths.SORT_INDEX]: 1 });
  } else {
    if (lang) {
      dbFind[`${langTextsPath}.${lang}`] = {
        $regex: new RegExp(`(^|.*\\s+)${escapeStringRegexp(text)}(\\s+.*|$)`), // whole word match within a language
        $options: 'i',
      };
    }

    // TODO: Mongo text search can do language specific stemming, by setting $language
    dbFind = {
      ...dbFind,
      $text: { $search: `"${text}"`, $language, $diacriticSensitive: matchAccents },
    };

    cursor = dbCollection.aggregate([
      { $match: dbFind },
      { $sort: { score: { $meta: 'textScore' }, [DbPaths.SORT_INDEX]: 1 } },
    ]);
  }

  // eslint-disable-next-line no-console
  console.log(`Searching ${dbCollectionEntries(dictionaryId)}...`, dbFind);

  // STEP: 5: Return counts only or full search result
  if (countTotalOnly) {
    // countDocuments might not be 100%, but should be more than the actual count, so it would page to the end
    const count = await dbCollection.countDocuments(dbFind);
    // eslint-disable-next-line no-console
    console.log('Found count', count);
    return Response.success({ count });
  }

  const entries = await cursor.skip(getDbSkip(pageNumber, pageLimit)).limit(pageLimit).toArray();
  // eslint-disable-next-line no-console
  console.log(`Found ${entries.length} entries`, entries[0]);
  return entries.length ? Response.success(entries) : Response.notFound();
}

export default handler;
