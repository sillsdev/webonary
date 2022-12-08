import { APIGatewayProxyResult } from 'aws-lambda';

export type ResponseBody = string | object | [object];

function buildError(statusCode: number, error: ResponseBody): APIGatewayProxyResult {
  return buildResponse(statusCode, typeof error === 'string' ? { message: error } : error);
}

function buildResponse(
  statusCode: number,
  responseBody: ResponseBody,
  // eslint-disable-next-line @typescript-eslint/ban-types
  header?: object,
): APIGatewayProxyResult {
  const response: APIGatewayProxyResult = {
    statusCode,
    headers: {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Credentials': true,
    },
    body: '',
  };

  if (header) {
    response.headers = { ...response.headers, ...header };
  }

  if (typeof responseBody === 'string') {
    response.headers = { ...response.headers, 'Content-Type': 'text/plain' };
    response.body = responseBody;
  } else {
    response.body = JSON.stringify(responseBody);
  }

  return response;
}

export function success(body: ResponseBody): APIGatewayProxyResult {
  return buildResponse(200, body);
}

export function redirect(location: string): APIGatewayProxyResult {
  return buildResponse(302, '', { Location: location });
}

export function badRequest(error = 'Bad request'): APIGatewayProxyResult {
  return buildError(400, error);
}

export function unauthorized(error = 'Unauthorized'): APIGatewayProxyResult {
  return buildError(401, error);
}

export function forbidden(error = 'Forbidden'): APIGatewayProxyResult {
  return buildError(403, error);
}

export function notFound(error = 'Not found'): APIGatewayProxyResult {
  return buildError(404, error);
}

export function failure(error = 'Internal Server Error'): APIGatewayProxyResult {
  return buildError(500, error);
}
