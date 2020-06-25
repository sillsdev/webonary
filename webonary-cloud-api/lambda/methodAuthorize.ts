import { CustomAuthorizerEvent, APIGatewayAuthorizerResult, Context, Callback } from 'aws-lambda';
import axios from 'axios';

import { getBasicAuthCredentials } from './utils';

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

  const authHeaders = event.headers?.Authorization;
  const dictionaryId = event.pathParameters?.dictionaryId;

  if (dictionaryId && authHeaders) {
    try {
      const credentials = getBasicAuthCredentials(authHeaders);

      // Same user should have the same access to post dictionary entry data as well as files.
      // User access is per dictionary per user.
      const principalId = `${dictionaryId}::${credentials.username}`;

      // To allow for correct caching behavior, we use wildcards for method (POST or DELETE) and path
      const resource = event.methodArn.replace(
        /(POST\/post|DELETE\/delete)\/(dictionary|entry|file)\//i,
        '*/*/',
      );

      // Call Webonary.org for user authentication
      axios.defaults.headers.post['Content-Type'] = 'application/json';
      const authPath = `${process.env.WEBONARY_URL}/${dictionaryId}${process.env.WEBONARY_AUTH_PATH}`;

      const response = await axios.post(authPath, '{}', {
        auth: credentials,
      });

      console.log(`Processing policy for ${credentials.username} to resource ${event.methodArn}`);
      if (response.status === 200) {
        if (response.data) {
          console.log(`Denied ${principalId} to resource ${resource} for ${response.data}`);
          return callback(null, generatePolicy(principalId, 'Deny', resource)); // 403
        }
        console.log(`Created policy for ${principalId} to access resource ${resource}`);
        return callback(null, generatePolicy(principalId, 'Allow', resource));
      }
    } catch (error) {
      return callback(error);
    }
  }

  return callback('Unauthorized');
}

export default handler;
