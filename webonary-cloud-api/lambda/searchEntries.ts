import { APIGatewayEvent, Context, Callback } from 'aws-lambda';
import { MongoClient } from 'mongodb';
import { connectToDB } from './mongo';
import { DB_NAME, COLLECTION_ENTRIES } from './db';
import * as response from './response';

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
    const dictionary = event.pathParameters?.dictionary;
    const fullText = event.queryStringParameters?.fullText;

    // NOTE: Mongo text search can do language specific stemming,
    // but then each search much specify correct language in a field named "language".
    // To use this, we will need to distinguish between lang field in Entry, and a special language field
    // that is one of the valid Mongo values, or "none".
    // By setting $language: "none" in all queries and in index, we are skipping language-specific stemming.
    // If we wanted to use language stemming, then we must specify language in each search,
    // and UNION all searches if language-independent search is desired
    const $language = event.queryStringParameters?.language ?? 'none';
    const $text = { $search: fullText ?? '', $language };

    const entries = await db
      .collection(COLLECTION_ENTRIES)
      .find({ dictionary, $text })
      .toArray();
    if (!entries.length) {
      return callback(null, response.notFound([{}]));
    }
    return callback(null, response.success(entries));
  } catch (err) {
    return callback(`Error while performing ${JSON.stringify(event)}: ${JSON.stringify(err)}`);
  } finally {
    await dbClient.close();
  }
}

export default handler;
