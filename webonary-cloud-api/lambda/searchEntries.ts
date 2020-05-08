import { APIGatewayEvent, Context, Callback } from 'aws-lambda';
import { MongoClient } from 'mongodb';
import { connectToDB } from './mongo';
import {
  DB_NAME,
  DB_COLLECTION_ENTRIES,
  PATH_TO_DEFINITION_VALUE,
  PATH_TO_MAIN_HEADWORD_VALUE,
  PATH_TO_PART_OF_SPEECH_VALUE,
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
    const lang = event.queryStringParameters?.lang; // this is used to limit which language to search
    const mainLang = event.queryStringParameters?.mainLang; // main language of the dictionary

    const text = event.queryStringParameters?.text ?? '';
    const partOfSpeech = event.queryStringParameters?.partOfSpeech ?? '';
    const matchPartial = event.queryStringParameters?.matchPartial ?? '';
    const matchAccents = event.queryStringParameters?.matchAccents ?? ''; // NOTE: matching accent works only for fulltext searching

    let errorMessage = '';
    if (!text) {
      errorMessage = 'Text to search must be specified.';
    }

    if (errorMessage) {
      return callback(null, Response.badRequest(errorMessage));
    }

    let entries;
    let primaryFilter;
    let langFilter;
    const $regex = new RegExp(text, 'i');

    if (partOfSpeech) {
      primaryFilter = {
        dictionaryId,
        [PATH_TO_PART_OF_SPEECH_VALUE]: partOfSpeech,
      };
    } else {
      primaryFilter = { dictionaryId };
    }

    if (lang) {
      let langFieldToFilter;
      if (mainLang && mainLang === lang) {
        langFieldToFilter = 'mainHeadWord';
      } else {
        langFieldToFilter = 'senses.definitionOrGloss';
      }

      langFilter = {
        [langFieldToFilter]: {
          $elemMatch: {
            lang,
            value: {
              $regex,
            },
          },
        },
      };
    } else {
      langFilter = {
        $or: [
          { [PATH_TO_MAIN_HEADWORD_VALUE]: { $regex } },
          { [PATH_TO_DEFINITION_VALUE]: { $regex } },
        ],
      };
    }

    if (matchPartial === '1') {
      const dictionaryPartialSearch = {
        $and: [{ ...primaryFilter }, { ...langFilter }],
      };
      entries = await db
        .collection(DB_COLLECTION_ENTRIES)
        .find(dictionaryPartialSearch)
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
      const $text = { $search: text, $language, $diacriticSensitive };
      const dictionaryFulltextSearch = { ...primaryFilter, $text };
      if (lang) {
        entries = await db
          .collection(DB_COLLECTION_ENTRIES)
          .aggregate([{ $match: dictionaryFulltextSearch }, { $match: langFilter }])
          .toArray();
      } else {
        entries = await db
          .collection(DB_COLLECTION_ENTRIES)
          .find(dictionaryFulltextSearch)
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
