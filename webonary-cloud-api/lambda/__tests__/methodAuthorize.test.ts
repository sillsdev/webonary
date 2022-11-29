/* eslint-disable no-console */
import { CustomAuthorizerEvent, Context } from 'aws-lambda';
import axios from 'axios';
import lambdaHandler from '../methodAuthorize';

jest.mock('axios');
const mockedAxios = axios as jest.Mocked<typeof axios>;

const username = 'testUser';
const password = 'testPassword!';
const base64Credentials = Buffer.from(`${username}:${password}`).toString('base64');
const headers = { Authorization: `Basic ${base64Credentials}` };

const dictionaryId = 'testDictionary';

const event: CustomAuthorizerEvent = {
  type: 'testEventType',
  methodArn: `arn:something-something/POST/post/entry/${dictionaryId}`,
  pathParameters: { dictionaryId },
  headers,
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
      Promise.resolve({
        status: 200,
        data: `${dictionaryId.toLowerCase()},another-dictionary`,
      }),
    );
    await lambdaHandler(event, context, (error, result) => {
      expect(error).toBe(null);
      expect(result.principalId).toEqual(username);
      expect(result.policyDocument.Statement[0].Effect).toBe('Allow');
      expect(result.policyDocument.Statement[0].Action).toBe('execute-api:Invoke');
      expect(result.policyDocument.Statement[0].Resource).toStrictEqual([
        `arn:something-something/*/*/${dictionaryId.toLowerCase()}`,
        'arn:something-something/*/*/another-dictionary',
        `arn:something-something/*/*/${dictionaryId}`,
      ]);
    });

    return expect.hasAssertions();
  });

  test('auth denied invalid username or password', async (): Promise<void> => {
    mockedAxios.post.mockRejectedValueOnce({ response: { status: 401 } });
    await lambdaHandler(event, context, (error, result) => {
      expect(error).toBe(null);
      expect(result.principalId).toEqual(username);
      expect(result.policyDocument.Statement[0].Action).toBe('execute-api:Invoke');
      expect(result.policyDocument.Statement[0].Effect).toBe('Deny');
      expect(result.policyDocument.Statement[0].Resource).toStrictEqual([
        `arn:something-something/*/*/${dictionaryId}`,
      ]);
    });

    return expect.hasAssertions();
  });

  test('auth denied for dictionary', async (): Promise<void> => {
    mockedAxios.post.mockImplementation(() =>
      Promise.resolve({ status: 200, data: 'another-dictionary' }),
    );

    await lambdaHandler(event, context, (error) => {
      expect(error).toBe('Unauthorized');
    });

    return expect.hasAssertions();
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

  test('auth throws an unknown error', async (): Promise<void> => {
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
