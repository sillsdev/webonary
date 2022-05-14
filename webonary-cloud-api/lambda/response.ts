export const BAD_REQUEST = 'BadRequest';

type ResponseBody = string | object;

export interface Response {
  statusCode: number;
  headers: object;
  body: string;
}

function buildResponse(statusCode: number, response: ResponseBody, header?: object): Response {
  let headers = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Credentials': true,
  };

  if (header) {
    headers = { ...headers, ...header };
  }

  let body = response;
  if (typeof response === 'string') {
    const contentType = {
      'Content-Type': 'text/plain',
    };
    headers = { ...headers, ...contentType };
  } else {
    body = JSON.stringify(response);
  }

  return { statusCode, headers, body: body as string };
}

export function success(body: ResponseBody): Response {
  return buildResponse(200, body);
}

export function badRequest(body: ResponseBody): Response {
  return buildResponse(400, { errorType: BAD_REQUEST, errorMessage: body });
}

export function failure(body: ResponseBody): Response {
  return buildResponse(500, body);
}

export function notFound(body: ResponseBody): Response {
  return buildResponse(404, body);
}

export function redirect(location: string): Response {
  return buildResponse(302, '', { Location: location });
}
