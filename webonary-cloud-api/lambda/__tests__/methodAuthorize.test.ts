/* eslint-disable no-console */
import { CustomAuthorizerEvent, Context } from 'aws-lambda';
import axios from 'axios';
import lambdaHandler from '../methodAuthorize';

jest.mock('axios');
const mockedAxios = axios as jest.Mocked<typeof axios>;

function constructHeaders(username: string, password: string) {
  const base64Credentials = Buffer.from(`${username}:${password}`).toString('base64');
  return { Authorization: `Basic ${base64Credentials}` };
}

const username = 'testUser';
const password = 'testPassword!';
const dictionaryId = 'testDictionary';

const event: CustomAuthorizerEvent = {
  type: 'testEventType',
  methodArn: `arn:something-something/POST/post/entry/${dictionaryId}`,
  pathParameters: { dictionaryId },
  headers: constructHeaders(username, password),
};

const context: Context = {
  callbackWaitsForEmptyEventLoop: false,
  functionName: '',
  functionVersion: '',
  invokedFunctionArn: '',
  memoryLimitInMB: '',
  awsRequestId: '',
  logGroupName: '',
  logStreamName: '',
  getRemainingTimeInMillis: () => 0,
  done: () => undefined,
  fail: () => undefined,
  succeed: () => undefined,
};

describe('methodAuthorize', () => {
  test('successful auth', async (): Promise<void> => {
    mockedAxios.post.mockImplementation(() =>
      Promise.resolve({ status: 200, data: 'test-dictionary1,test-dictionary2' }),
    );
    await lambdaHandler(event, context, (error, result) => {
      expect(error).toBe(null);
      expect(result.policyDocument.Statement[0].Effect).toBe('Allow');
      expect(result.policyDocument.Statement[0].Action).toBe('execute-api:Invoke');
      expect(result.policyDocument.Statement[0].Resource).toStrictEqual([
        'arn:something-something/*/*/test-dictionary1',
        'arn:something-something/*/*/test-dictionary2',
      ]);
    });

    return expect.hasAssertions();
  });

  test('principalId is a hash of auth header', async () => {
    mockedAxios.post.mockImplementation(() =>
      Promise.resolve({ status: 200, data: 'test-dictionary' }),
    );
    await lambdaHandler(event, context, (error, result) => {
      expect(result.principalId).toEqual('c877f338f4db1f933af876a3bd68ecefa834a0a0');
    });

    return expect.hasAssertions();
  });

  test('when password changes, principalId changes', async () => {
    await lambdaHandler(
      { ...event, headers: constructHeaders(username, 'password1') },
      context,
      async (unused1, result1) => {
        await lambdaHandler(
          { ...event, headers: constructHeaders(username, 'password2') },
          context,
          (unused2, result2) => {
            expect(result1.principalId).not.toEqual(result2.principalId);
          },
        );
      },
    );

    return expect.hasAssertions();
  });

  test('auth denied', async (): Promise<void> => {
    mockedAxios.post.mockImplementation(() => Promise.resolve({ status: 401, data: 'some error' }));

    await lambdaHandler(event, context, (error, result) => {
      expect(error).toBe(null);
      expect(result.policyDocument.Statement[0].Effect).toBe('Deny');
      expect(result.policyDocument.Statement[0].Action).toBe('execute-api:Invoke');
    });

    await expect.assertions(3);
  });

  test('auth error no headers', async (): Promise<void> => {
    mockedAxios.post.mockImplementation(() => Promise.resolve({}));

    const emptyEvent: CustomAuthorizerEvent = {
      type: '',
      methodArn: '',
    };

    await lambdaHandler(emptyEvent, context, (error) => {
      expect(error).toBe('Unauthorized');
    });

    return expect.hasAssertions();
  });

  test('auth error generic', async (): Promise<void> => {
    mockedAxios.post.mockImplementation(() => Promise.resolve({ status: 500, data: 'some error' }));

    await lambdaHandler(event, context, (error) => {
      expect(error).toBe('Unauthorized');
    });

    return expect.hasAssertions();
  });

  test('auth throws error', async (): Promise<void> => {
    const errorMessage = 'threw an error';
    mockedAxios.post.mockImplementation(() => {
      throw new Error(errorMessage);
    });

    await lambdaHandler(event, context, (error) => {
      expect(error).toBe('Unauthorized');
    });

    return expect.hasAssertions();
  });
});
