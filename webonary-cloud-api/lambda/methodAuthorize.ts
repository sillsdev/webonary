import { CustomAuthorizerEvent, APIGatewayAuthorizerResult, Context, Callback } from 'aws-lambda';
import axios, { AxiosError } from 'axios';

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
  const dictionaryPath = event.pathParameters?.dictionaryId ?? '';
  const dictionaryId = dictionaryPath.toLowerCase();

  if (!dictionaryId || !authHeaders) {
    return callback('Unauthorized');
  }

  const credentials = getBasicAuthCredentials(authHeaders);
  // eslint-disable-next-line no-console
  console.log(`Processing policy for ${credentials.username} to resource ${event.methodArn}`);

  // Same user should have the same access to post/delete dictionary entry data as well as files.
  const principalId = credentials.username;
  const resourceRegex = /(POST\/post|DELETE\/delete)\/(dictionary|entry|file)\/(.+)$/i;

  // Call Webonary.org for user authentication
  axios.defaults.headers.post['Content-Type'] = 'application/json';
  const authPath = `${process.env.WEBONARY_URL}/${dictionaryId}${process.env.WEBONARY_AUTH_PATH}`;

  try {
    const response = await axios.post(authPath, '{}', { auth: credentials });
    // eslint-disable-next-line no-console
    console.log(
      `Received auth response for ${credentials.username}: ${response.status} ${response.data}`,
    );

    if (response.status !== 200 || !response.data) {
      return callback('Unauthorized');
    }

    const allowedDictionaries = response.data.split(','); // will always lowercase
    if (!allowedDictionaries.includes(dictionaryId)) {
      return callback('Unauthorized');
    }

    if (dictionaryId !== dictionaryPath) {
      // All dictionary ids should be lowercase.
      // But if user entered differently in FLex, let it access under that path as well.
      allowedDictionaries.push(dictionaryPath);
    }

    const resources = allowedDictionaries.map((id: string) => {
      // To allow for correct caching behavior, we use wildcards for method (POST or DELETE) and path. E.g.:
      // arn:aws:execute-api:region:zz:zzz/prod/POST/post/dictionary/myDictionary will be replaced with
      // arn:aws:execute-api:region:zz:zzz/prod/*/*/myDictionary
      return event.methodArn.replace(resourceRegex, `*/*/${id}`);
    });

    // eslint-disable-next-line no-console
    console.log(`Creating policy for ${principalId} to access ${resources}`);
    return callback(null, generatePolicy(principalId, 'Allow', resources));
  } catch (error) {
    const axiosError = error as AxiosError;
    if (axiosError.response?.status === 401) {
      const resources = [event.methodArn.replace(resourceRegex, `*/*/${dictionaryPath}`)];

      // eslint-disable-next-line no-console
      console.log(`Denying ${principalId} to access ${resources}: ${axiosError.response.data}`);
      return callback(null, generatePolicy(principalId, 'Deny', resources)); // 403
    }

    // eslint-disable-next-line no-console
    console.log(`Unknown error for ${principalId} to access ${dictionaryId}`, axiosError.response);
    return callback('Unauthorized');
  }
}

export default handler;
