/* eslint-disable no-console */
import { CustomAuthorizerEvent, Context } from 'aws-lambda';
import axios from 'axios';
import lambdaHandler from '../loadAuthorize';

jest.mock('axios');
const mockedAxios = axios as jest.Mocked<typeof axios>;

const username = 'testUser';
const password = 'testPassword!';
const base64Credentials = Buffer.from(`${username}:${password}`).toString('base64');
const headers = { Authorization: `Basic ${base64Credentials}` };

const dictionaryId = 'testDictionary';

const event: CustomAuthorizerEvent = {
  type: 'testEventType',
  methodArn: 'POST/load/entry/',
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

describe('loadAuthorize', () => {
  test('successful auth', async (): Promise<void> => {
    mockedAxios.post.mockImplementation(() => Promise.resolve({ status: 200, data: '' }));
    await lambdaHandler(event, context, (error, result) => {
      expect(error).toBe(null);
      expect(result.principalId).toEqual(`${dictionaryId}::${username}`);
      expect(result.policyDocument.Statement[0].Effect).toBe('Allow');
      expect(result.policyDocument.Statement[0].Action).toBe('execute-api:Invoke');
      expect(result.policyDocument.Statement[0].Resource).toBe('POST/load/*/');
    });

    return expect.hasAssertions();
  });

  test('auth denied', async (): Promise<void> => {
    mockedAxios.post.mockImplementation(() => Promise.resolve({ status: 200, data: 'some error' }));

    await lambdaHandler(event, context, (error, result) => {
      expect(error).toBe(null);
      expect(result.principalId).toEqual(`${dictionaryId}::${username}`);
      expect(result.policyDocument.Statement[0].Effect).toBe('Deny');
      expect(result.policyDocument.Statement[0].Action).toBe('execute-api:Invoke');
    });

    return expect.hasAssertions();
  });

  test('auth error no headers', async (): Promise<void> => {
    mockedAxios.post.mockImplementation(() => Promise.resolve({}));

    const emptyEvent: CustomAuthorizerEvent = {
      type: '',
      methodArn: '',
    };

    try {
      await lambdaHandler(emptyEvent, context, error => {
        expect(error).toBe('Unauthorized');
      });
    } catch (error) {
      console.log(error);
    }

    return expect.hasAssertions();
  });

  test('auth error generic', async (): Promise<void> => {
    mockedAxios.post.mockImplementation(() => Promise.resolve({ status: 500, data: 'some error' }));

    try {
      await lambdaHandler(event, context, error => {
        expect(error).toBe('Unauthorized');
      });
    } catch (error) {
      console.log(error);
    }

    return expect.hasAssertions();
  });

  test('auth throws error', async (): Promise<void> => {
    const errorMessage = 'threw an error';
    try {
      mockedAxios.post.mockImplementation(() => {
        throw new Error(errorMessage);
      });

      await lambdaHandler(event, context, error => {
        expect(error).toEqual(new Error(errorMessage));
      });
    } catch (error) {
      console.log(error);
    }

    return expect.hasAssertions();
  });
});
