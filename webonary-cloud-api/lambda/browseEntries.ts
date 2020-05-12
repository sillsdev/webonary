import { APIGatewayEvent, Context, Callback } from 'aws-lambda';
import { MongoClient } from 'mongodb';
import { connectToDB } from './mongo';
import {
  DB_NAME,
  DB_MAX_DOCUMENTS_PER_CALL,
  DB_COLLECTION_ENTRIES,
  DB_COLLATION_LOCALE_DEFAULT,
  DB_COLLATION_LOCALE_STRENGTH,
  DB_COLLATION_LOCALES,
  PATH_TO_ENTRY_MAIN_HEADWORD_VALUE,
  PATH_TO_ENTRY_DEFINITION,
  PATH_TO_ENTRY_DEFINITION_LANG,
  PATH_TO_ENTRY_DEFINITION_VALUE,
  DbFindParameters,
  EntryData,
} from './db';
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
    const lang = event.queryStringParameters?.lang;
    const pageNumber = Number(event.queryStringParameters?.pageNumber) || 1;
    const pageLimit = Number(event.queryStringParameters?.pageLimit) || DB_MAX_DOCUMENTS_PER_CALL;
    const skip = (pageNumber - 1) * pageLimit;

    let errorMessage = '';

    if (!text) {
      errorMessage = 'Browse letter head must be specified.';
    } else if (pageNumber < 1) {
      errorMessage = 'Page number cannot be less than 1.';
    } else if (pageLimit > DB_MAX_DOCUMENTS_PER_CALL || pageLimit < 1) {
      errorMessage = `Page limit cannot be greater than ${DB_MAX_DOCUMENTS_PER_CALL} or less than 1.`;
    }

    if (errorMessage) {
      return callback(null, Response.badRequest(errorMessage));
    }

    let entries: EntryData[];
    const dbFind: DbFindParameters = {};
    let dbLocale = DB_COLLATION_LOCALE_DEFAULT;
    let dbSortKey: string;

    dbFind.dictionaryId = dictionaryId;

    if (lang) {
      dbFind.reversalLetterHeads = { lang, value: text };
      // TODO: Include reversal language in sorting
      dbSortKey = PATH_TO_ENTRY_MAIN_HEADWORD_VALUE;
      if (DB_COLLATION_LOCALES.includes(lang)) {
        dbLocale = lang;
      }

      entries = await db
        .collection(DB_COLLECTION_ENTRIES)
        .aggregate(
          [
            { $match: dbFind },
            { $unwind: `$${PATH_TO_ENTRY_DEFINITION}` },
            { $match: { [PATH_TO_ENTRY_DEFINITION_LANG]: lang } },
            { $sort: { [PATH_TO_ENTRY_DEFINITION_VALUE]: 1 } },
          ],
          { collation: { locale: dbLocale, strength: DB_COLLATION_LOCALE_STRENGTH } },
        )
        .skip(skip)
        .limit(pageLimit)
        .toArray();
    } else {
      dbFind.letterHead = text;
      // TODO: Make sure to set default sort for entries to be on main headword browse letter and value
      dbSortKey = PATH_TO_ENTRY_MAIN_HEADWORD_VALUE;

      entries = await db
        .collection(DB_COLLECTION_ENTRIES)
        .find(dbFind)
        .collation({ locale: dbLocale, strength: DB_COLLATION_LOCALE_STRENGTH })
        .sort({ [dbSortKey]: 1 })
        .skip(skip)
        .limit(pageLimit)
        .toArray();
    }

    if (!entries.length) {
      return callback(null, Response.notFound([{}]));
    }

    /* Manual sorting, if necessary
    let entriesSorted: EntryData[] = [];
    if (lang) {
      entriesSorted = entries.sort((a, b) => {
        const aWord = a.senses.definitionOrGloss.find(letter => letter.lang === lang);
        const bWord = b.senses.definitionOrGloss.find(letter => letter.lang === lang);
        if (aWord && bWord) {
          return aWord.value.localeCompare(bWord.value);
        }
        return 0;
      });
    } else {
      entriesSorted = entries.sort((a, b) => {
        return a.mainHeadWord[0].value.localeCompare(b.mainHeadWord[0].value);
      });
    }
    return callback(null, Response.success(entriesSorted));
    */
    return callback(null, Response.success(entries));
  } catch (error) {
    // eslint-disable-next-line no-console
    console.log(error);
    return callback(null, Response.failure({ errorType: error.name, errorMessage: error.message }));
  }
}

export default handler;
