import { APIGatewayEvent, Context, Callback } from 'aws-lambda';
import { MongoClient } from 'mongodb';
import { connectToDB } from './mongo';
import {
  DB_NAME,
  DB_COLLATION_INSENSITIVE,
  DB_COLLECTION_ENTRIES,
  PATH_TO_SEM_DOMS_KEY,
  PATH_TO_SEM_DOMS_LANG,
  PATH_TO_SEM_DOMS_VALUE,
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

    const text = event.queryStringParameters?.text ?? '';

    let errorMessage = '';
    if (!text) {
      errorMessage = 'Text to search semantic domains must be specified.';
    }

    if (errorMessage) {
      return callback(null, Response.badRequest(errorMessage));
    }

    /*    
    const filter: object = { dictionaryId, [PATH_TO_SEM_DOMS_VALUE]: text };

    const semDoms = await db
      .collection(DB_COLLECTION_ENTRIES)
      .find(filter, DB_COLLATION_INSENSITIVE)
      .project({
        _id: 0,
        [PATH_TO_SEM_DOMS_KEY]: 1,
        [PATH_TO_SEM_DOMS_LANG]: 1,
        [PATH_TO_SEM_DOMS_VALUE]: 1,
      })
      .toArray();
*/

    const filter: object = {
      dictionaryId,
      [PATH_TO_SEM_DOMS_VALUE]: text,
    };

    const semDoms = await db
      .collection(DB_COLLECTION_ENTRIES)
      .distinct(PATH_TO_SEM_DOMS_VALUE, {}, collation: DB_COLLATION_INSENSITIVE);

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
