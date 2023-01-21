import { Db } from 'mongodb';
import { DbPaths } from './entry.model';

export const MONGO_DB_NAME = process.env.MONGO_DB_NAME as string;

export const DB_MAX_DOCUMENTS_PER_CALL = 100;
export const DB_MAX_UPDATES_PER_CALL = 50;

// TODO: Vietnamese seems to have the most Latin diacritics, so use this for insensitive search
// We might have to do RegExp searches instead to get more accurate insensitive searches
export const DB_COLLATION_LOCALE_DEFAULT_FOR_INSENSITIVITY = 'vi';

export enum DbCollationStrength {
  INSENSITIVITY = 1, // case insensitive and diacritical insensitive search
  CASE_INSENSITIVITY = 2, // case insensitive but diacritical sensitive search
  SENSITIVITY = 3, // case sensitive and diacritical sensitive search
}

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
export const DB_COLLECTION_REVERSALS = 'webonaryReversals';

export const reversalEntryId = ({ dictionaryId, guid }: { dictionaryId: string; guid: string }) =>
  `${dictionaryId}::${guid}`;

export const dbCollectionEntries = (dictionaryId: string) =>
  `${DB_COLLECTION_ENTRIES}_${dictionaryId}`;

export const createEntriesIndexes = async (db: Db, dictionaryId: string) => {
  const collection = dbCollectionEntries(dictionaryId);

  await Promise.all([
    // browsing by letter head
    db.collection(collection).createIndex({
      [DbPaths.LETTER_HEAD]: 1,
      [DbPaths.SORT_INDEX]: 1,
    }),
    // fulltext index (case and diacritic insensitive by default)
    db.collection(collection).createIndex(
      {
        [DbPaths.ENTRY_MAIN_HEADWORD_VALUE]: 'text',
        [DbPaths.ENTRY_HEADWORD_VALUE]: 'text',
        [DbPaths.ENTRY_CITATION_FORM_VALUE]: 'text',
        [DbPaths.ENTRY_LEXEME_FORM_VALUE]: 'text',
        [DbPaths.ENTRY_DEFINITION_OR_GLOSS_VALUE]: 'text',
        [DbPaths.ENTRY_DEFINITION_VALUE]: 'text',
        [DbPaths.ENTRY_GLOSS_VALUE]: 'text',
        [DbPaths.ENTRY_SEARCH_TEXTS]: 'text',
      },
      {
        weights: {
          [DbPaths.ENTRY_MAIN_HEADWORD_VALUE]: 10,
          [DbPaths.ENTRY_HEADWORD_VALUE]: 10,
          [DbPaths.ENTRY_CITATION_FORM_VALUE]: 9,
          [DbPaths.ENTRY_LEXEME_FORM_VALUE]: 7,
          [DbPaths.ENTRY_DEFINITION_OR_GLOSS_VALUE]: 5,
          [DbPaths.ENTRY_DEFINITION_VALUE]: 5,
          [DbPaths.ENTRY_GLOSS_VALUE]: 5,
        },
        default_language: 'none',
      },
    ),
  ]);
  // case and diacritic insensitive index for semantic domains
  await db.collection(collection).createIndex(
    {
      [DbPaths.ENTRY_SEM_DOMS_NAME_VALUE]: 1,
    },
    {
      collation: {
        locale: DB_COLLATION_LOCALE_DEFAULT_FOR_INSENSITIVITY,
        strength: DbCollationStrength.CASE_INSENSITIVITY,
      },
    },
  );
};

export const createIndexes = async (db: Db) => {
  // reversal browsing
  await db.collection(DB_COLLECTION_REVERSALS).createIndex({
    [DbPaths.DICTIONARY_ID]: 1,
    [DbPaths.ENTRY_REVERSAL_FORM_LANG]: 1,
    [DbPaths.LETTER_HEAD]: 1,
    [DbPaths.SORT_INDEX]: 1,
  });
};

export const dropIndexes = async (db: Db) => {
  await db.collection(DB_COLLECTION_REVERSALS).dropIndexes();
};
