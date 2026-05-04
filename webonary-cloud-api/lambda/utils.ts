/* eslint-disable @typescript-eslint/no-explicit-any */
import { ListOptionItem } from './dictionary.model';
import { DictionaryEntry } from './entry.model';
import {APIGatewayProxyEventHeaders} from "aws-lambda/trigger/api-gateway-proxy";
import { APIGatewayRequestAuthorizerEventHeaders } from "aws-lambda";

export interface BasicAuthCredentials {
  username: string;
  password: string;
}

export function getBasicAuthCredentials(authHeaders: string): BasicAuthCredentials {
  const encodedCredentials = authHeaders.split(' ')[1];
  const [username, ...passParts] = Buffer.from(encodedCredentials, 'base64').toString().split(':');
  const password = passParts.join(':');

  return { username, password };
}

export function isMaintenanceMode() {
  return Boolean(process.env.MAINTENANCE_MODE);
}

export function maintenanceModeMessage() {
  return (
    process.env.MAINTENANCE_MODE_MESSAGE ||
    'This service is temporarily unavailable. Please try again later.'
  );
}

export function getDbSkip(pageNumber: number, pageLimit: number): number {
  return (pageNumber - 1) * pageLimit;
}

export function setSearchableEntries(entries: ListOptionItem[]): ListOptionItem[] {
  return entries.map((entry) => {
    const newEntry = entry;
    if ('name' in entry && typeof entry.name === 'string' && entry.name !== '')
      newEntry.nameInsensitive = entry.name.toLowerCase().normalize();
    return newEntry;
  });
}

export function sortEntries(entries: DictionaryEntry[], lang?: string): DictionaryEntry[] {
  let entriesSorted: DictionaryEntry[];
  if (lang !== '') {
    entriesSorted = entries.sort((a, b) => {
      const aWord = a.senses[0].definitionorgloss.find((letter) => letter.lang === lang);
      const bWord = b.senses[0].definitionorgloss.find((letter) => letter.lang === lang);
      if (aWord && bWord) {
        return aWord.value.localeCompare(bWord.value);
      }
      return 0;
    });
  } else {
    entriesSorted = entries.sort((a, b) => {
      return a.mainheadword[0].value.localeCompare(b.mainheadword[0].value);
    });
  }
  return entriesSorted;
}

export function removeDiacritics(text: string) {
  return text.normalize('NFD').replace(/\p{Diacritic}/gu, '');
}

// See https://github.com/sindresorhus/escape-string-regexp/blob/main/index.js
export function escapeStringRegexp(value: string) {
  return value.replace(/[|\\{}()[\]^$+*?.]/g, '\\$&').replace(/-/g, '\\x2d');
}

export function semanticDomainAbbrevRegex(abbrev: string) {
  return { $in: [abbrev, new RegExp(`^${escapeStringRegexp(abbrev)}.`, 'i')] };
}

export function getFieldWorksVersion(headers: APIGatewayProxyEventHeaders | null) {

  // return null if no user-agent header found
  if (!headers || !('User-Agent' in headers))
    return null;

  // expecting a string like "FieldWorks Language Explorer v.9.2.5.12345"
  const userAgent = headers['User-Agent'] ?? '';

  // if not FieldWorks, return null
  if (!userAgent.includes('FieldWorks'))
    return [userAgent];

  // the string should end with the version number
  const found = userAgent.match(/\d[\d.\-a-zA-Z]+$/);
  if (!found)
    return [userAgent];

  return found[0].split('.').map((val) => {
    return /^\d+$/.test(val) ? Number(val) : val;
  });
}

export function isFieldWorksVersionOK(headers: APIGatewayRequestAuthorizerEventHeaders | null): boolean {

  // return true if no user-agent header found
  if (!headers || !('user-agent' in headers))
    return true;

  // expecting a string like "FieldWorks Language Explorer v.9.2.5"
  const userAgent = headers['user-agent'] ?? '';

  // if not FieldWorks, return true
  if (!userAgent.includes('FieldWorks'))
    return true;

  // the string should end with the version number
  const found = userAgent.match(/\d[\d.]+$/);
  if (!found)
    return false;

  const minVersion = [9, 2, 5];
  const parts = found[0].split('.').map(Number);

  if (parts[0] > minVersion[0])
    return true;

  if (parts[0] < minVersion[0])
    return false;

  // parts[0] == minVersion[0]
  if (parts.length < 2)
    return false;

  if (parts[1] > minVersion[1])
    return true;

  if (parts[1] < minVersion[1])
    return false;

  // parts[0] == minVersion[0] and parts[1] == minVersion[1]
  if (parts.length < 3)
    return false;

  return (parts[2] >= minVersion[2]);
}

