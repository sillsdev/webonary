export interface DictionaryLanguage {
  lang: string;
  title?: string;
  letters?: string[];
  cssFiles?: string[];
}

export interface DictionaryData {
  _id?: string;
  mainLanguage: DictionaryLanguage;
  reversalLanguages: DictionaryLanguage[];
}

export interface PostDictionary {
  id: string;
  data: DictionaryData;
}

export interface EntryFile {
  id: string;
  src: string;
  fileClass?: string;
  caption?: string;
}

export interface EntryValue {
  lang: string;
  value: string;
  type?: string;
}

export interface EntrySense {
  definitionOrGloss: EntryValue[];
  partOfSpeech: EntryValue;
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

export interface DbFindParameters {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  [key: string]: any;
}

export const DB_NAME = process.env.DB_NAME as string;
export const COLLECTION_DICTIONARIES = 'webonaryDictionaries';
export const COLLECTION_ENTRIES = 'webonaryEntries';
export const DB_MAX_UPDATES_PER_CALL = 50;
