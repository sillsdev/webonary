import { CustomAuthorizerEvent, APIGatewayAuthorizerResult, Context, Callback } from 'aws-lambda';
import axios from 'axios';

type Effect = 'Allow' | 'Deny';

function generatePolicy(
  principalId: string,
  effect: Effect,
  resource: string,
): APIGatewayAuthorizerResult {
  const authResult: APIGatewayAuthorizerResult = {
    principalId,
    policyDocument: {
      Version: '2012-10-17',
      Statement: [
        {
          Action: 'execute-api:Invoke',
          Effect: effect,
          Resource: resource,
        },
      ],
    },
  };

  return authResult;
}

export async function handler(
  event: CustomAuthorizerEvent,
  context: Context,
  callback: Callback,
): Promise<void> {
  // eslint-disable-next-line no-param-reassign
  context.callbackWaitsForEmptyEventLoop = false;

  const dictionaryId = event.pathParameters?.dictionaryId;
  const authHeaders = event.headers?.Authorization;

  if (dictionaryId && authHeaders) {
    try {
      const encodedCredentials = authHeaders.split(' ')[1];
      const plainCredentials = Buffer.from(encodedCredentials, 'base64')
        .toString()
        .split(':');
      const username = plainCredentials[0];
      const password = plainCredentials[1];

      // Same user should have the same access to post dictionary entry data as well as files.
      // User access is per dictionary per user.
      const principalId = `${dictionaryId}::${username}`;

      // To allow for correct caching behavior, we only keep the first part of request (post) and use wildcard for the next
      const resource = event.methodArn.replace(
        /(POST\/post|DELETE\/delete)\/(dictionary|entry|file)\//i,
        '$1/*/',
      );

      // Call Webonary.org for user authentication
      axios.defaults.headers.post['Content-Type'] = 'application/json';
      const authPath = `${process.env.WEBONARY_URL}/${dictionaryId}${process.env.WEBONARY_AUTH_PATH}`;

      const response = await axios.post(authPath, '{}', {
        auth: { username, password },
      });

      if (response.status === 200) {
        if (response.data) {
          return callback(null, generatePolicy(principalId, 'Deny', resource)); // 403
        }

        return callback(null, generatePolicy(principalId, 'Allow', resource));
      }
    } catch (error) {
      return callback(error);
    }
  }

  return callback('Unauthorized');
}

export default handler;
