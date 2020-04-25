export const BAD_REQUEST = 'BadRequest';

type ResponseBody = string | object;

function buildResponse(statusCode: number, response: ResponseBody, header?: object): object {
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

  return { statusCode, headers, body };
}

export function success(body: ResponseBody): object {
  return buildResponse(200, body);
}

export function badRequest(body: ResponseBody): object {
  // This is to mimic default message structure from AWS Gateway custom authorizer
  return buildResponse(400, { ErrorType: BAD_REQUEST, Message: body });
}

export function failure(body: ResponseBody): object {
  return buildResponse(500, body);
}

export function notFound(body: ResponseBody): object {
  return buildResponse(404, body);
}

export function redirect(location: string): object {
  return buildResponse(302, '', { Location: location });
}
