import { APIGatewayEvent, Context, Callback } from 'aws-lambda';
import { MongoClient } from 'mongodb';
import { connectToDB } from './mongo';
import {
  DB_NAME,
  DB_COLLECTION_DICTIONARIES,
  DB_COLLECTION_DICTIONARY_ENTRIES,
  DB_COLLECTION_REVERSAL_ENTRIES,
} from './db';
import { Dictionary } from './dictionary.model';
import { DbPaths } from './entry.model';

import { DbFindParameters } from './base.model';
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
    const dbFind: DbFindParameters = { dictionaryId };

    dbClient = await connectToDB();
    const db = dbClient.db(DB_NAME);
    const dbItem: Dictionary | null = await db
      .collection(DB_COLLECTION_DICTIONARIES)
      .findOne({ _id: dictionaryId });

    if (!dbItem) {
      return callback(null, Response.notFound({}));
    }

    // get total entries
    dbItem.mainLanguage.entriesCount = await db
      .collection(DB_COLLECTION_DICTIONARY_ENTRIES)
      .countDocuments(dbFind);

    // get reversal entry counts
    const reversalEntriesCounts = await Promise.all(
      dbItem.reversalLanguages.map(async ({ lang }) => {
        dbFind[DbPaths.ENTRY_REVERSAL_FORM_LANG] = lang;
        return db.collection(DB_COLLECTION_REVERSAL_ENTRIES).countDocuments(dbFind);
      }),
    );

    reversalEntriesCounts.forEach((entriesCount, index) => {
      dbItem.reversalLanguages[index].entriesCount = entriesCount;
    });

    return callback(null, Response.success(dbItem));
  } catch (error) {
    // eslint-disable-next-line no-console
    console.log(error);
    return callback(null, Response.failure({ errorType: error.name, errorMessage: error.message }));
  }
}

export default handler;
