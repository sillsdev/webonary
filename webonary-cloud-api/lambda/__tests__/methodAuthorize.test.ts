/* eslint-disable no-console */
import { APIGatewayRequestAuthorizerEvent } from 'aws-lambda';
import axios from 'axios';
import lambdaHandler from '../methodAuthorize';

jest.mock('axios');
const mockedAxios = axios as jest.Mocked<typeof axios>;

const username = 'testUser';
const password = 'testPassword!';
const base64Credentials = Buffer.from(`${username}:${password}`).toString('base64');
const headers = { Authorization: `Basic ${base64Credentials}` };

const dictionaryId = 'testDictionary';

const event: Partial<APIGatewayRequestAuthorizerEvent> = {
  methodArn: `arn:something-something/POST/post/entry/${dictionaryId}`,
  pathParameters: { dictionaryId },
  headers,
};

describe('methodAuthorize', () => {
  test('successful auth', async (): Promise<void> => {
    mockedAxios.post.mockImplementation(() =>
      Promise.resolve({
        status: 200,
        data: `${dictionaryId.toLowerCase()},another-dictionary`,
      }),
    );
    const result = await lambdaHandler(event as APIGatewayRequestAuthorizerEvent);
    expect(result.principalId).toEqual(username);
    expect(result.policyDocument.Statement[0]).toStrictEqual({
      Action: 'execute-api:Invoke',
      Effect: 'Allow',
      Resource: [
        `arn:something-something/*/*/${dictionaryId.toLowerCase()}`,
        'arn:something-something/*/*/another-dictionary',
        `arn:something-something/*/*/${dictionaryId}`,
      ],
    });
  });

  test('auth denied invalid username or password', async (): Promise<void> => {
    mockedAxios.post.mockRejectedValueOnce({ response: { status: 401 } });

    const result = await lambdaHandler(event as APIGatewayRequestAuthorizerEvent);
    expect(result.principalId).toEqual(username);
    expect(result.policyDocument.Statement[0]).toStrictEqual({
      Action: 'execute-api:Invoke',
      Effect: 'Deny',
      Resource: [`arn:something-something/*/*/${dictionaryId}`],
    });
  });

  test('auth denied for dictionary', async (): Promise<void> => {
    mockedAxios.post.mockImplementation(() =>
      Promise.resolve({ status: 200, data: 'another-dictionary' }),
    );

    await expect(lambdaHandler(event as APIGatewayRequestAuthorizerEvent)).rejects.toThrow(
      'Unauthorized',
    );
  });

  test('auth error no headers', async (): Promise<void> => {
    mockedAxios.post.mockImplementation(() => Promise.resolve({}));

    const emptyEvent: Partial<APIGatewayRequestAuthorizerEvent> = {
      methodArn: '',
    };

    await expect(lambdaHandler(emptyEvent as APIGatewayRequestAuthorizerEvent)).rejects.toThrow(
      'Unauthorized',
    );
  });

  test('auth error generic', async (): Promise<void> => {
    mockedAxios.post.mockImplementation(() => Promise.resolve({ status: 500, data: 'some error' }));

    await expect(lambdaHandler(event as APIGatewayRequestAuthorizerEvent)).rejects.toThrow(
      'Unauthorized',
    );
  });

  test('auth throws an unknown error', async (): Promise<void> => {
    const errorMessage = 'threw an error';
    mockedAxios.post.mockImplementation(() => {
      throw new Error(errorMessage);
    });

    await expect(lambdaHandler(event as APIGatewayRequestAuthorizerEvent)).rejects.toThrow(
      'Unauthorized',
    );
  });
});
