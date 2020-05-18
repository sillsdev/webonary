/* eslint-disable @typescript-eslint/no-explicit-any */
import { DictionaryEntry, EntryValueItem } from './structs';

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

    toSubKeys.forEach(subKey => {
      const subKeyLowerCase = subKey.toLowerCase();
      const fromObjectKey = Object.keys(fromObject).find(
        key => key.toLowerCase() === subKeyLowerCase,
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
  Object.keys(toObject).forEach(toObjectKey => {
    if (hasKey(toObject, toObjectKey)) {
      const toObjectKeyLowercase = (toObjectKey ?? '').toLowerCase();
      const fromObjectKey = Object.keys(fromObject).find(
        key => key.toLowerCase() === toObjectKeyLowercase,
      );

      if (fromObjectKey && hasKey(fromObject, fromObjectKey)) {
        if (typeof toObject[toObjectKey] === 'object') {
          let toObjectSubKeys: string[] = [];
          if (Array.isArray(toObject[toObjectKey])) {
            const subArray = Object.entries(toObject[toObjectKey] ?? [{}]);
            toObjectSubKeys = Object.keys(subArray[0][1]);
          } else {
            toObjectSubKeys = Object.keys(toObject[toObjectKey] ?? {});
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

export function setSearchableEntries(entries: EntryValueItem[]): EntryValueItem[] {
  return entries.map(entry => {
    const newEntry = entry;
    if ('value' in entry && typeof entry.value === 'string' && entry.value !== '')
      newEntry.valueInsensitive = entry.value.toLowerCase().normalize();
    return newEntry;
  });
}

export function sortEntries(entries: DictionaryEntry[], lang?: string): DictionaryEntry[] {
  let entriesSorted: DictionaryEntry[] = [];
  if (lang !== '') {
    entriesSorted = entries.sort((a, b) => {
      const aWord = a.senses[0].definitionOrGloss.find(letter => letter.lang === lang);
      const bWord = b.senses[0].definitionOrGloss.find(letter => letter.lang === lang);
      if (aWord && bWord) {
        return aWord.value.localeCompare(bWord.value);
      }
      return 0;
    });
  } else {
    entriesSorted = entries.sort((a, b) => {
      return a.mainHeadWord[0].value.localeCompare(b.mainHeadWord[0].value);
    });
  }
  return entriesSorted;
}
