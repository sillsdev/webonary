import { APIGatewayEvent, Context, Callback } from 'aws-lambda';
import { MongoClient, UpdateWriteOpResult } from 'mongodb';
import { connectToDB, success, DB_NAME, COLLECTION_ENTRIES } from './mongo';

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

    const entries = JSON.parse(event.body as string);

    await db.collection(COLLECTION_ENTRIES).createIndex(
      {
        'mainHeadWord.value': 'text',
        'senses.definitionOrGloss.value': 'text',
      },
      { name: 'wordsFulltextIndex', default_language: 'none' },
    );

    const updatedAt = new Date().toUTCString();
    if (Array.isArray(entries)) {
      const promises = entries.map(
        (entry): Promise<UpdateWriteOpResult> => {
          return db
            .collection(COLLECTION_ENTRIES)
            .updateOne({ _id: entry._id }, { $set: { ...entry, updatedAt } }, { upsert: true });
        },
      );

      await Promise.all(promises);
      return callback(null, success({ updatedAt }));
    }
    return callback(null, 'Input must be an array of entries');
  } catch (err) {
    return callback('Error occurred while loadEntry');
  } finally {
    await dbClient.close();
  }
}

export default handler;
