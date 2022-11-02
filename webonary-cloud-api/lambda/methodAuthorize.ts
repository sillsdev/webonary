import { APIGatewayAuthorizerResult, Callback, Context, CustomAuthorizerEvent } from 'aws-lambda';
import axios from 'axios';
import { createHash } from 'crypto';

import { getBasicAuthCredentials } from './utils';

type Effect = 'Allow' | 'Deny';

function generatePolicy(
  principalId: string,
  effect: Effect,
  resources: string[],
): APIGatewayAuthorizerResult {
  return {
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
}

function getPrincipalId(authorizationHeader: string): string {
  const sha1 = createHash('sha1');
  sha1.update(authorizationHeader);
  return sha1.digest('hex');
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

  if (!dictionaryId || !authHeaders) {
    return callback('Unauthorized');
  }

  const credentials = getBasicAuthCredentials(authHeaders);

  // eslint-disable-next-line no-console
  console.log(`Processing policy for ${credentials.username} to resource ${event.methodArn}`);
  // Same user should have the same access to post/delete dictionary entry data as well as files.
  const principalId = getPrincipalId(authHeaders);
  const resourceRegex = /(POST\/post|DELETE\/delete)\/(dictionary|entry|file)\/(.+)$/i;

  async function getAuthPolicy() {
    try {
      // Call Webonary.org for user authentication
      axios.defaults.headers.post['Content-Type'] = 'application/json';
      const authPath = `${process.env.WEBONARY_URL}/${dictionaryId}${process.env.WEBONARY_AUTH_PATH}`;
      const response = await axios.post(authPath, '{}', {
        auth: credentials,
      });

      if (response.status === 401) {
        return generatePolicy(principalId, 'Deny', [
          event.methodArn.replace(resourceRegex, `*/*/${dictionaryId}`),
        ]);
      }

      if (response.status !== 200 || !response.data) {
        return null;
      }

      const resources = response.data.split(',').map((id: string) => {
        // To allow for correct caching behavior, we use wildcards for method (POST or DELETE) and path. E.g.:
        // arn:aws:execute-api:region:zz:zzz/prod/POST/post/dictionary/myDictionary will be replaced with
        // arn:aws:execute-api:region:zz:zzz/prod/*/*/dictionary/myDictionary
        return event.methodArn.replace(resourceRegex, `*/*/${id}`);
      });

      // eslint-disable-next-line no-console
      console.log(`Creating policy for ${principalId} to access ${resources}`);
      return generatePolicy(principalId, 'Allow', resources);
    } catch (error) {
      // eslint-disable-next-line no-console
      console.log(`Not generating an auth policy because of error: ${error}`);
      return null;
    }
  }

  const authPolicy = await getAuthPolicy();
  if (!authPolicy) {
    return callback('Unauthorized');
  }

  return callback(null, await getAuthPolicy());
}

export default handler;
