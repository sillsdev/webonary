import { APIGatewayProxyResult } from 'aws-lambda';

export function parseGuids(response: APIGatewayProxyResult): string[] {
  return (
    JSON.parse(response.body)
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      .map((entry: any) => entry.guid)
      .filter((guid: string) => guid)
  );
}
