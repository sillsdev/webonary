import { APIGatewayEvent, Context, Callback } from 'aws-lambda';
import { MongoClient } from 'mongodb';
import { connectToDB } from './mongo';
import { DB_NAME, PATH_TO_SEM_DOMS_SEARCH_VALUE, DB_COLLECTION_DICTIONARIES } from './db';
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

    const text = event.queryStringParameters?.text.toLowerCase().normalize() ?? '';

    let errorMessage = '';
    if (!text) {
      errorMessage = 'Text to search semantic domains must be specified.';
    }

    if (errorMessage) {
      return callback(null, Response.badRequest(errorMessage));
    }

    let filter: object = { dictionaryId, [PATH_TO_SEM_DOMS_SEARCH_VALUE]: text };

    if (lang) {
      filter = Object.assign(filter, { lang });
    }

    const semDoms = await db
      .collection(DB_COLLECTION_DICTIONARIES)
      .distinct('semanticDomains', filter);

    if (!semDoms.length) {
      return callback(null, Response.notFound([{}]));
    }

    return callback(null, Response.success(semDoms));
  } catch (error) {
    // eslint-disable-next-line no-console
    console.log(error);
    return callback(null, Response.failure({ errorType: error.name, errorMessage: error.message }));
  }
}

export default handler;
