import { CustomAuthorizerEvent, APIGatewayAuthorizerResult, Context, Callback } from 'aws-lambda';
import axios from 'axios';

import { getBasicAuthCredentials } from './utils';

type Effect = 'Allow' | 'Deny';

function generatePolicy(
  principalId: string,
  effect: Effect,
  resources: string[],
): APIGatewayAuthorizerResult {
  const authResult: APIGatewayAuthorizerResult = {
    principalId,
    policyDocument: {
      Version: '2012-10-17',
      Statement: [
        {
          Action: 'execute-api:Invoke',
          Effect: effect,
          Resource: resources,
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
    const credentials = getBasicAuthCredentials(authHeaders);

    console.log(`Processing policy for ${credentials.username} to resource ${event.methodArn}`);
    // Same user should have the same access to post/delete dictionary entry data as well as files.
    const principalId = credentials.username;
    const resourceRegex = /(POST\/post|DELETE\/delete)\/(dictionary|entry|file)\/(.+)$/i;

    // Call Webonary.org for user authentication
    axios.defaults.headers.post['Content-Type'] = 'application/json';
    const authPath = `${process.env.WEBONARY_URL}/${dictionaryId}${process.env.WEBONARY_AUTH_PATH}`;

    try {
      const response = await axios.post(authPath, '{}', {
        auth: credentials,
      });

      if (response.status === 200 && response.data) {
        const resources = response.data.split(',').map((id: string) => {
          // To allow for correct caching behavior, we use wildcards for method (POST or DELETE) and path
          // arn:aws:execute-api:region:zz:zzz/prod/POST/post/dictionary/myDictionary will be replaced with
          // arn:aws:execute-api:region:zz:zzz/prod/*/*/dictionary/myDictionary will be replaced with
          return event.methodArn.replace(resourceRegex, `*/*/${id}`);
        });

        console.log(`Creating policy for ${principalId} to access ${resources}`);
        return callback(null, generatePolicy(principalId, 'Allow', resources));
      }
    } catch (error) {
      const resources = [event.methodArn.replace(resourceRegex, `*/*/${dictionaryId}`)];

      console.log(`Denying ${principalId} to access {resources}`);
      return callback(null, generatePolicy(principalId, 'Deny', resources)); // 403
    }
  }

  return callback('Unauthorized');
}

export default handler;
