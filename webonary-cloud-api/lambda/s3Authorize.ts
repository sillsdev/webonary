import { APIGatewayEvent, Context, Callback } from 'aws-lambda';
import * as AWS from 'aws-sdk';

const s3 = new AWS.S3({ signatureVersion: 'v4' });
const bucket = process.env.S3_BUCKET;
if (!bucket) {
  throw Error('S3 bucket not set');
}

export async function handler(
  event: APIGatewayEvent,
  context: Context,
  callback: Callback,
): Promise<void> {
  try {
    const { objectId, action } = JSON.parse(event.body as string);

    if (!objectId) {
      throw Error('S3 object key missing');
    }
    if (action !== 'putObject' && action !== 'getObject') {
      throw Error('Action not allowed');
    }

    return callback(null, {
      statusCode: 200,
      headers: { 'Content-Type': 'text/plain' },
      body: s3.getSignedUrl(action, {
        Bucket: bucket,
        Key: objectId,
        Expires: 100,
      }),
    });
  } catch (error) {
    return callback(`Error occurred in S3 Authorizer: ${JSON.stringify(error)}`);
  }
}

export default handler;
