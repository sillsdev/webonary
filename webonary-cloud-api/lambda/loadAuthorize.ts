import { CustomAuthorizerEvent, APIGatewayAuthorizerResult, Context, Callback } from 'aws-lambda';
import axios from 'axios';

axios.defaults.baseURL = process.env.WEBONARY_URL;
axios.defaults.headers.post['Content-Type'] = 'application/json';

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

  if (event.headers) {
    const dictionary = event.pathParameters?.dictionary;

    const encodedCreds = event.headers.Authorization.split(' ')[1];
    const plainCreds = Buffer.from(encodedCreds, 'base64')
      .toString()
      .split(':');
    const username = plainCreds[0];
    const password = plainCreds[1];

    // Same user should have the same access to load dictionary entry data as well as files.
    // User access is per dictionary per user.
    const principalId = `${dictionary}::${username}`;

    // To allow for correct caching behavior, we only keep the first part of request (load) and use wildcard for the next
    const resource = event.methodArn.replace(/POST\/load\/(entry|file)\//i, 'POST/load/*/');

    // Call Webonary.org for user authentication
    try {
      const response = await axios.post(`/${dictionary}/wp-json/webonary/import`, '{}', {
        auth: { username, password },
      });

      if (response.status === 200) {
        if (response.data) {
          return callback(null, generatePolicy(principalId, 'Deny', resource)); // 403
        }

        return callback(null, generatePolicy(principalId, 'Allow', resource));
      }
    } catch (error) {
      return callback(`Error: ${JSON.stringify(error.response)}`);
    }
  }

  return callback('Unauthorized');
}

export default handler;
