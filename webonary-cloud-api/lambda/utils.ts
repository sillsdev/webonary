/* eslint-disable @typescript-eslint/no-explicit-any */
import { ListOptionItem } from './dictionary.model';
import { DictionaryEntry } from './entry.model';

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
  let entriesSorted: DictionaryEntry[] = [];
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
