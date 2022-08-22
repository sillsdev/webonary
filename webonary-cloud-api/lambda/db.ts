import { Db } from 'mongodb';
import { DbPaths } from './entry.model';

export const MONGO_DB_NAME = process.env.MONGO_DB_NAME as string;

export const DB_MAX_DOCUMENTS_PER_CALL = 100;
export const DB_MAX_UPDATES_PER_CALL = 50;

// TODO: Vietnamese seems to have the most Latin diacritics, so use this for insensitive search
// We might have to do RegExp searches instead to get more accurate insensitive searches
export const DB_COLLATION_LOCALE_DEFAULT_FOR_INSENSITIVITY = 'vi';
export const DB_COLLATION_STRENGTH_FOR_INSENSITIVITY = 1; // case insensitive and diacritical insensitive search
export const DB_COLLATION_STRENGTH_FOR_CASE_INSENSITIVITY = 2; // case insensitive but diacritical sensitive search
export const DB_COLLATION_STRENGTH_FOR_SENSITIVITY = 3; // case sensitive and diacritical sensitive search

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
export const DB_COLLECTION_DICTIONARY_ENTRIES = 'webonaryEntries';
export const DB_COLLECTION_REVERSAL_ENTRIES = 'webonaryReversals';

export const entriesFulltextIndexName = 'wordsFulltextIndex';
export const entriesHeadwordIndexName = `${DbPaths.ENTRY_MAIN_HEADWORD_LANG}_1_${DbPaths.ENTRY_MAIN_HEADWORD_VALUE}_1`;

export const createIndexes = async (db: Db) => {
  // fulltext index (case and diacritic insensitive by default)
  await db.collection(DB_COLLECTION_DICTIONARY_ENTRIES).createIndex(
    {
      [DbPaths.ENTRY_DISPLAY_TEXT]: 'text',
    },
    {
      name: entriesFulltextIndexName,
      default_language: 'none',
    },
  );

  // case and diacritic insensitive index for semantic domains
  await db.collection(DB_COLLECTION_DICTIONARY_ENTRIES).createIndex(
    {
      [DbPaths.ENTRY_MAIN_HEADWORD_LANG]: 1,
      [DbPaths.ENTRY_MAIN_HEADWORD_VALUE]: 1,
    },
    {
      name: entriesHeadwordIndexName,
      collation: {
        locale: DB_COLLATION_LOCALE_DEFAULT_FOR_INSENSITIVITY,
        strength: DB_COLLATION_STRENGTH_FOR_CASE_INSENSITIVITY,
      },
    },
  );
};

export const dropIndexes = async (db: Db) => {
  await db.collection(DB_COLLECTION_DICTIONARY_ENTRIES).dropIndexes();
};
