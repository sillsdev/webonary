/**
 * @api {post} /load/file/:dictionary Load file signed URL
 * @apiDescription Calling this API will return a signed URL which can be used to upload a file temporarily to a protected S3 bucked.
 * @apiName LoadDictionaryFileSignedUrl
 * @apiGroup Dictionary
 *
 * @apiPermission dictionary admin in Webonary
 *
 * @apiUse BasicAuthHeader
 *
 * @apiParam (Path) {String} :dictionary Unique dictionary id registered in <a href=https://www.webonary.org>Webonary</a>
 *
 * @apiParam (Post Body) {String} objectId  Relative file path, starting with dictionary name
 * @apiParam (Post Body) {Object} action    "putObject"
 *
 * @apiParamExample {json} Post Body Example
 * {
 *	 "objectId": "moore/pictures/Vitex_doniana.jpg",
 *   "action": "putObject"
 * }
 *
 * @apiSuccess {String} URL Signed URL to upload file to AWS S3 Bucket
 *
 * @apiSuccessExample Success Response Example
 * HTTP/1.1 200 OK
 * https://webonarycloudapistack-dictionarybucket8e4dd392-sy1tdtrywp2d.s3.us-east-2.amazonaws.com/moore/pictures/Vitex_doniana.jpg?X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=ASIAVNTQMPQG64WZTDW4%2F20200424%2Fus-east-2%2Fs3%2Faws4_request&X-Amz-Date=20200424T201152Z&X-Amz-Expires=100&X-Amz-Security-Token=IQoJb3JpZ2luX2VjEFQaCXVzLWVhc3QtMiJGMEQCIFPO0pCHDhkhW3EFiWGUe5Bsq6C2G9H8SaUmrgsjPADOAiB3pH3B4S0KMIk88bR5JbksUWCvsrb40UTbWJBM9ccZ2ir1AQh9EAEaDDM3MjgyNDExMjE0MSIMhk12v83ixdFJeUctKtIB7lqjJ8ncHjuJRWxo0a4ALEgt0fgdsY%2FZD%2BF3hzDMBLR1Sur4W%2BP0OJViYijJEfA1btMVgulek5fsPCmkiIDICsl3gkzpryXvyiKfTZPcQ%2B1kzGvyD7SSzof6YE3Nj3piHAfhTvMg0nqhFWcEZUArXZVjJqznMIgMrWAHgblYU4vVZ%2Bo70jp5TNxAmEDhN0hNVaBVifuzYt7YKZQN0iyg90izNAtDjTYprbn0WEP8%2BD45vH8fDcqKWrmfOSLdkszNjaX7lFco8kvrACvn9x7xbl61MPSPjfUFOuEB1IR%2FZGIYZyMNK1WaaBW9o2TL8n9h8YDipJ5ar54DJXe9VvBD85O7%2BU0P8PBP5IXYkLHcxSvk2Fif30a%2BcoDVXP7QCYzfybviQ%2FblokbQHtkDQ1xZpC%2BnBcft4lkX8lTIMN2Ppg5kUFzPpj6nIegMNFDfhigAQwvBvq9MKbjBo503im%2B6%2FtH4GIuJ185BOO1biBalrT4k18DKzbABNI%2BeIGps5TkJYywIl0I5Ow5LTO9yiwbgR%2BohsAO8xgHIfxUA65ELNChrUKUY2h1eQnCyZdDScxKCHz6tt3839NcG%2F4bJ&X-Amz-Signature=e6d9fa96efa464e065db54b351d42b37069dc7e48cbb73679e5a383f3ba12a56&X-Amz-SignedHeaders=host
 *
 * @apiError BadRequest Input should be an be an object containing objectId and action ("putObject").
 *
 * @apiErrorExample {json} BadRequest Example
 * HTTP/1.1 400 BadRequest
 * {
 *    "ErrorType": "BadRequest",
 *    "Message": "Missing objectId in the request body"
 * }
 *
 * @apiUse ErrorForbidden
 */

/**
 * @api {put} https://AWS_SIGNED_URL Load file
 * @apiDescription This signed URL will temporarily allow uploading a file to a protected S3 bucket.
 * @apiName LoadDictionaryFile
 * @apiGroup Dictionary
 * 
 * @apiExample {curl} Example usage
 * curl --location --request PUT \ 
'https://webonarycloudapistack-dictionarybucket8e4dd392-sy1tdtrywp2d.s3.us-east-2.amazonaws.com/moore/pictures/Vitex_doniana.jpg?X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=ASIAVNTQMPQG64WZTDW4%2F20200424%2Fus-east-2%2Fs3%2Faws4_request&X-Amz-Date=20200424T201152Z&X-Amz-Expires=100&X-Amz-Security-Token=IQoJb3JpZ2luX2VjEFQaCXVzLWVhc3QtMiJGMEQCIFPO0pCHDhkhW3EFiWGUe5Bsq6C2G9H8SaUmrgsjPADOAiB3pH3B4S0KMIk88bR5JbksUWCvsrb40UTbWJBM9ccZ2ir1AQh9EAEaDDM3MjgyNDExMjE0MSIMhk12v83ixdFJeUctKtIB7lqjJ8ncHjuJRWxo0a4ALEgt0fgdsY%2FZD%2BF3hzDMBLR1Sur4W%2BP0OJViYijJEfA1btMVgulek5fsPCmkiIDICsl3gkzpryXvyiKfTZPcQ%2B1kzGvyD7SSzof6YE3Nj3piHAfhTvMg0nqhFWcEZUArXZVjJqznMIgMrWAHgblYU4vVZ%2Bo70jp5TNxAmEDhN0hNVaBVifuzYt7YKZQN0iyg90izNAtDjTYprbn0WEP8%2BD45vH8fDcqKWrmfOSLdkszNjaX7lFco8kvrACvn9x7xbl61MPSPjfUFOuEB1IR%2FZGIYZyMNK1WaaBW9o2TL8n9h8YDipJ5ar54DJXe9VvBD85O7%2BU0P8PBP5IXYkLHcxSvk2Fif30a%2BcoDVXP7QCYzfybviQ%2FblokbQHtkDQ1xZpC%2BnBcft4lkX8lTIMN2Ppg5kUFzPpj6nIegMNFDfhigAQwvBvq9MKbjBo503im%2B6%2FtH4GIuJ185BOO1biBalrT4k18DKzbABNI%2BeIGps5TkJYywIl0I5Ow5LTO9yiwbgR%2BohsAO8xgHIfxUA65ELNChrUKUY2h1eQnCyZdDScxKCHz6tt3839NcG%2F4bJ&X-Amz-Signature=e6d9fa96efa464e065db54b351d42b37069dc7e48cbb73679e5a383f3ba12a56&X-Amz-SignedHeaders=host' \
--header 'Content-Type: image/jpeg' \
--data-binary '@/tmp/moore/pictures/Vitex_doniana.jpg'
 *
 * @apiHeader Content-Type Valid mime type (e.g. image/jpeg, audio/mpeg, video/mpeg, etc)
 * @apiHeaderExample {Header} Header Example
 * "Content-Type: image/jpeg"
 *
 * @apiParamExample {file} Post Body Example
 * <file content here>

 * @apiSuccess {String} Empty string
 *
 * @apiSuccessExample Success Response Example
 * HTTP/1.1 200 OK
 *
 * @apiError Forbidden Access is denied if the temporarily signed URL is no longer valid.
 *
 * @apiErrorExample {xml} Forbidden Error Example
 * HTTP/1.1 403 Forbidden
 * <?xml version="1.0" encoding="UTF-8"?>
 * <Error>
 *   <Code>AccessDenied</Code>
 *   <Message>Request has expired</Message>
 *   <X-Amz-Expires>120</X-Amz-Expires>
 *   <Expires>2020-04-25T03:48:04Z</Expires>
 *   <ServerTime>2020-04-25T03:50:33Z</ServerTime>
 *   <RequestId>BF97CB31633F3C94</RequestId>
 *   <HostId>WNR6dm5Dlr/B5hs+izoGaBueMObntiXi9D1q+SmvT06FuDfmCNx4c5pNhpw1HcHB75KlMYP6SSs=</HostId>
 * </Error>
 */

import { APIGatewayEvent, Context, Callback } from 'aws-lambda';
import * as AWS from 'aws-sdk';
import * as Response from './response';

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

    let errorMessage = '';

    if (!objectId) {
      errorMessage = 'Missing objectId in the request body';
    }
    if (action !== 'putObject' && action !== 'getObject') {
      errorMessage = 'Invalid action in the request body';
    }

    if (errorMessage) {
      return callback(null, Response.badRequest(errorMessage));
    }

    const signedUrl = s3.getSignedUrl(action, {
      Bucket: bucket,
      Key: objectId,
      Expires: 120, // seconds
    });

    return callback(null, Response.success(signedUrl));
  } catch (error) {
    return callback(`Error occurred in S3 Authorizer: ${JSON.stringify(error)}`);
  }
}

export default handler;
