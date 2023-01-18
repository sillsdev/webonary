/* eslint-disable @typescript-eslint/no-explicit-any */
import { ListOptionItem } from './dictionary.model';
import { DictionaryEntry } from './entry.model';

export interface BasicAuthCredentials {
  username: string;
  password: string;
}

export function getBasicAuthCredentials(authHeaders: string): BasicAuthCredentials {
  const encodedCredentials = authHeaders.split(' ')[1];
  const [username, password] = Buffer.from(encodedCredentials, 'base64').toString().split(':');

  return { username, password };
}

export function hasKey<O>(obj: O, key: keyof any): key is keyof O {
  return key in obj;
}

export function getDbSkip(pageNumber: number, pageLimit: number): number {
  return (pageNumber - 1) * pageLimit;
}

// TODO: This copies all the values of a key, but does not do so iteratively.
// Once data coming from FLex is more settled, then we should do iterative copying per object/array for better type checking.
export function copyObjectKeyValueIgnoreKeyCase(
  toSubKeys: string[],
  fromKey: string,
  fromParentObject: any,
): any {
  const isArray = Array.isArray(fromParentObject[fromKey]);
  const fromObjectArray = isArray
    ? fromParentObject[fromKey]
    : new Array(fromParentObject[fromKey]);

  const toObjectArray = fromObjectArray.map((fromObject: any) => {
    const toObject: { [key: string]: any } = {};

    toSubKeys.forEach((subKey) => {
      const subKeyLowerCase = subKey.toLowerCase();
      const fromObjectKey = Object.keys(fromObject).find(
        (key) => key.toLowerCase() === subKeyLowerCase,
      );
      if (fromObjectKey && hasKey(fromObject, fromObjectKey)) {
        toObject[subKey] = fromObject[fromObjectKey];
      }
    });

    return toObject;
  });

  return isArray ? toObjectArray : toObjectArray[0];
}

export function copyObjectIgnoreKeyCase(toObject: object, fromObject: object): object {
  const copyObject: any = toObject;
  Object.keys(toObject).forEach((toObjectKey) => {
    const toObjectKeyLowercase = toObjectKey.toLowerCase();

    if (hasKey(toObject, toObjectKey)) {
      const fromObjectKey = Object.keys(fromObject).find(
        (key) => key.toLowerCase() === toObjectKeyLowercase,
      );

      if (fromObjectKey && hasKey(fromObject, fromObjectKey)) {
        if (typeof toObject[toObjectKey] === 'object') {
          let toObjectSubKeys: string[] = [];
          if (Array.isArray(toObject[toObjectKey])) {
            const subArray = Object.entries(toObject[toObjectKey]);
            const subArrayObject = subArray[0][1] as object;
            toObjectSubKeys = Object.keys(subArrayObject);
          } else {
            toObjectSubKeys = Object.keys(toObject[toObjectKey]);
          }

          copyObject[toObjectKey] = copyObjectKeyValueIgnoreKeyCase(
            toObjectSubKeys,
            fromObjectKey,
            fromObject,
          );
        } else {
          copyObject[toObjectKey] = fromObject[fromObjectKey];
        }
      }
    }
  });
  return copyObject;
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

export function semanticDomainAbbrevRegex(abbrev: string) {
  return { $in: [abbrev, new RegExp(`^${abbrev}.`)] };
}
