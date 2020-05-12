export interface EntryFile {
  id: string;
  src: string;
  fileClass?: string;
  caption?: string;
}

export interface EntryValue {
  key?: string;
  lang: string;
  value: string;
  searchValue?: string; // lowercase and normalized
}

export interface EntrySense {
  definitionOrGloss: EntryValue[];
  partOfSpeech: EntryValue;
  semanticDomains?: EntryValue[];
}

export interface EntryData {
  _id?: string;
  dictionaryId: string;
  letterHead: string;
  mainHeadWord: EntryValue[];
  pronunciations: EntryValue[];
  senses: EntrySense;
  reversalLetterHeads: EntryValue[];
  audio: EntryFile;
  pictures: EntryFile[];
}

export interface PostEntry {
  guid: string;
  data: EntryData;
}

export interface DictionaryLanguage {
  lang: string;
  title?: string;
  entriesCount?: number;
  letters?: string[];
  partsOfSpeech?: string[];
  cssFiles?: string[];
}

export interface DictionaryData {
  _id?: string;
  mainLanguage: DictionaryLanguage;
  reversalLanguages: DictionaryLanguage[];
  semanticDomains?: EntryValue[];
}

export interface PostDictionary {
  id: string;
  data: DictionaryData;
}

export interface DbFindParameters {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  [key: string]: any;
}

export const DB_NAME = process.env.DB_NAME as string;

export const DB_MAX_DOCUMENTS_PER_CALL = 100;
export const DB_MAX_UPDATES_PER_CALL = 50;

// Vietnamese seem to have the most Latin diacritics, so use this for case and diacritic insensitive search
export const DB_COLLATION_LOCALE_DEFAULT = 'vi';
export const DB_COLLATION_LOCALE_STRENGTH = 1;

// See https://docs.mongodb.com/manual/reference/collation-locales-defaults/#collation-languages-locales
export const DB_COLLATION_LOCALES = [
  'af',
  'sq',
  'am',
  'ar',
  'hy',
  'as',
  'az',
  'be',
  'bn',
  'bs',
  'bg',
  'my',
  'ca',
  'chr',
  'zh',
  'zh_Hant',
  'hr',
  'cs',
  'da',
  'nl',
  'dz',
  'en',
  'en_US',
  'en_US_POSIX',
  'eo',
  'et',
  'ee',
  'fo',
  'fil',
  'fr',
  'fr_CA',
  'gl',
  'ka',
  'de',
  'de_AT',
  'el',
  'gu',
  'ha',
  'haw',
  'he',
  'hi',
  'hu',
  'is',
  'ig',
  'smn',
  'id',
  'ga',
  'it',
  'ja',
  'kl',
  'kn',
  'kk',
  'kok',
  'ko',
  'ky',
  'lkt',
  'lo',
  'lv',
  'ln',
  'lt',
  'dsb',
  'lb',
  'mk',
  'ms',
  'ml',
  'mt',
  'mr',
  'mn',
  'ne',
  'se',
  'nb',
  'nn',
  'or',
  'om',
  'ps',
  'fa',
  'fa_AF',
  'pl',
  'pt',
  'pa',
  'ro',
  'ru',
  'sr',
  'sr_Latn',
  'si',
  'sk',
  'sl',
  'es',
  'sw',
  'sv',
  'ta',
  'te',
  'th',
  'bo',
  'to',
  'tr',
  'uk',
  'hsb',
  'ur',
  'ug',
  'vi',
  'wae',
  'cy',
  'yi',
  'yo',
  'zu',
];

export const DB_COLLECTION_DICTIONARIES = 'webonaryDictionaries';
export const DB_COLLECTION_ENTRIES = 'webonaryEntries';

export const PATH_TO_SEM_DOMS_KEY = 'semanticDomains.key';
export const PATH_TO_SEM_DOMS_LANG = 'semanticDomains.lang';
export const PATH_TO_SEM_DOMS_VALUE = 'semanticDomains.value';
export const PATH_TO_SEM_DOMS_SEARCH_VALUE = 'semanticDomains.searchValue';

export const PATH_TO_ENTRY_MAIN_HEADWORD_LANG = 'mainHeadWord.0.lang';
export const PATH_TO_ENTRY_MAIN_HEADWORD_VALUE = 'mainHeadWord.0.value';
export const PATH_TO_ENTRY_DEFINITION = 'senses.definitionOrGloss';
export const PATH_TO_ENTRY_DEFINITION_LANG = 'senses.definitionOrGloss.lang';
export const PATH_TO_ENTRY_DEFINITION_VALUE = 'senses.definitionOrGloss.value';
export const PATH_TO_ENTRY_PART_OF_SPEECH_VALUE = 'senses.partOfSpeech.value';
export const PATH_TO_ENTRY_SEM_DOMS_VALUE = `senses.${PATH_TO_SEM_DOMS_VALUE}`;

export function setSearchableEntries(entries: EntryValue[]): EntryValue[] {
  return entries.map(entry => {
    const newEntry = entry;
    newEntry.searchValue = entry.value.toLowerCase().normalize();
    return newEntry;
  });
}
