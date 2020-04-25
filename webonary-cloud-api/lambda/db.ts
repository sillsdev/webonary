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
  dictionary: string;
  letterHead: string;
  mainHeadWord: EntryValue[];
  pronunciations: EntryValue[];
  senses: EntrySense;
  reverseLetterHeads: EntryValue[];
  audio: EntryFile;
  pictures: EntryFile[];
}

export interface LoadEntry {
  guid: string;
  data: EntryData;
}

export interface DbFindParameters {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  [key: string]: any;
}

export const DB_NAME = process.env.DB_NAME as string;
export const COLLECTION_ENTRIES = 'webonaryEntries';
export const DB_MAX_UPDATES_PER_CALL = 50;
