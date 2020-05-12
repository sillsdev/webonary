import { APIGatewayEvent, Context, Callback } from 'aws-lambda';
import { MongoClient } from 'mongodb';
import { connectToDB } from './mongo';
import {
  DB_NAME,
  DB_COLLECTION_ENTRIES,
  DB_MAX_DOCUMENTS_PER_CALL,
  PATH_TO_ENTRY_DEFINITION_VALUE,
  PATH_TO_ENTRY_MAIN_HEADWORD_VALUE,
  PATH_TO_ENTRY_PART_OF_SPEECH_VALUE,
  PATH_TO_ENTRY_SEM_DOMS_VALUE,
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
    const searchSemDoms = event.queryStringParameters?.searchSemDoms ?? '';
    const partOfSpeech = event.queryStringParameters?.partOfSpeech ?? '';
    const matchPartial = event.queryStringParameters?.matchPartial ?? '';
    const matchAccents = event.queryStringParameters?.matchAccents ?? ''; // NOTE: matching accent works only for fulltext searching

    const pageNumber = Number(event.queryStringParameters?.pageNumber) || 1;
    const pageLimit = Number(event.queryStringParameters?.pageLimit) || DB_MAX_DOCUMENTS_PER_CALL;
    const skip = (pageNumber - 1) * pageLimit;

    let errorMessage = '';

    if (!text) {
      errorMessage = 'Text to search must be specified.';
    } else if (pageNumber < 1) {
      errorMessage = 'Page number cannot be less than 1.';
    } else if (pageLimit > DB_MAX_DOCUMENTS_PER_CALL || pageLimit < 1) {
      errorMessage = `Page limit cannot be greater than ${DB_MAX_DOCUMENTS_PER_CALL} or less than 1.`;
    }

    if (errorMessage) {
      return callback(null, Response.badRequest(errorMessage));
    }

    let entries;
    let primaryFilter = { dictionaryId };

    if (searchSemDoms === '1') {
      entries = await db
        .collection(DB_COLLECTION_ENTRIES)
        .find({ ...primaryFilter, [PATH_TO_ENTRY_SEM_DOMS_VALUE]: text })
        .skip(skip)
        .limit(pageLimit)
        .toArray();
    } else {
      let langFilter;
      const $regex = new RegExp(text, 'i');

      if (partOfSpeech) {
        primaryFilter = Object.assign(primaryFilter, {
          [PATH_TO_ENTRY_PART_OF_SPEECH_VALUE]: partOfSpeech,
        });
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
            { [PATH_TO_ENTRY_MAIN_HEADWORD_VALUE]: { $regex } },
            { [PATH_TO_ENTRY_DEFINITION_VALUE]: { $regex } },
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
          .skip(skip)
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
        const $text = { $search: text, $language, $diacriticSensitive };
        const dictionaryFulltextSearch = { ...primaryFilter, $text };
        if (lang) {
          entries = await db
            .collection(DB_COLLECTION_ENTRIES)
            .aggregate([{ $match: dictionaryFulltextSearch }, { $match: langFilter }])
            .skip(skip)
            .limit(pageLimit)
            .toArray();
        } else {
          entries = await db
            .collection(DB_COLLECTION_ENTRIES)
            .find(dictionaryFulltextSearch)
            .skip(skip)
            .limit(pageLimit)
            .toArray();
        }
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
