import { APIGatewayEvent, Context, Callback } from 'aws-lambda';
import { MongoClient } from 'mongodb';
import { connectToDB } from './mongo';
import {
  DB_NAME,
  DB_COLLECTION_DICTIONARIES,
  DB_COLLECTION_ENTRIES,
  PATH_TO_ENTRY_PART_OF_SPEECH_VALUE,
  DictionaryData,
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

  const dictionaryId = event.pathParameters?.dictionaryId;

  try {
    dbClient = await connectToDB();
    const db = dbClient.db(DB_NAME);
    const dbItem: DictionaryData | null = await db
      .collection(DB_COLLECTION_DICTIONARIES)
      .findOne({ _id: dictionaryId });
    if (!dbItem) {
      return callback(null, Response.notFound({}));
    }

    // get total entries
    dbItem.mainLanguage.entriesCount = await db
      .collection(DB_COLLECTION_ENTRIES)
      .countDocuments({ dictionaryId });

    // get all parts of speech
    const partsOfSpeech = await db
      .collection(DB_COLLECTION_ENTRIES)
      .distinct(PATH_TO_ENTRY_PART_OF_SPEECH_VALUE, { dictionaryId });

    dbItem.mainLanguage.partsOfSpeech = partsOfSpeech
      .filter(Boolean)
      .sort((a, b) => a.localeCompare(b));

    return callback(null, Response.success(dbItem));
  } catch (error) {
    // eslint-disable-next-line no-console
    console.log(error);
    return callback(null, Response.failure({ errorType: error.name, errorMessage: error.message }));
  }
}

export default handler;
