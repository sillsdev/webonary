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
  key?: string;
  lang: string;
  value: string;
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

export interface DbFindParameters {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  [key: string]: any;
}

export const DB_NAME = process.env.DB_NAME as string;

// Vietnamese seem to have the most Latin diacritics, so use this for case and diacritic insensitive search
export const DB_COLLATION_INSENSITIVE = { collation: { locale: 'vi', strength: 1 } };

export const DB_COLLECTION_DICTIONARIES = 'webonaryDictionaries';
export const DB_COLLECTION_ENTRIES = 'webonaryEntries';

export const PATH_TO_MAIN_HEADWORD_VALUE = 'mainHeadWord.value';

export const PATH_TO_DEFINITION_VALUE = 'senses.definitionOrGloss.value';

export const PATH_TO_PART_OF_SPEECH_VALUE = 'senses.partOfSpeech.value';

export const PATH_TO_SEM_DOMS_KEY = 'senses.semanticDomains.key';
export const PATH_TO_SEM_DOMS_LANG = 'senses.semanticDomains.lang';
export const PATH_TO_SEM_DOMS_VALUE = 'senses.semanticDomains.value';

export const DB_MAX_UPDATES_PER_CALL = 50;
