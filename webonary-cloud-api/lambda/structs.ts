/* eslint-disable max-classes-per-file */
export interface EntryFile {
  id: string;
  src: string;
  fileClass?: string;
  caption?: string;
}

export class EntryFileItem implements EntryFile {
  id = '';

  src = '';

  fileClass? = '';

  caption? = '';
}

export interface EntryValue {
  lang: string;
  value: string;
  guid?: string;
  key?: string;
  valueInsensitive?: string; // lowercase and normalized
}

export class EntryValueItem implements EntryValue {
  lang = '';

  value = '';

  guid? = '';

  key? = '';

  valueInsensitive? = '';
}

export interface EntryAnalysis {
  partOfSpeech: EntryValue[];
}

export class EntryAnalysisItem implements EntryAnalysis {
  partOfSpeech = Array(new EntryValueItem());
}

export interface EntryExampleContent {
  example: EntryValue[];
}

export class EntryExampleContentItem implements EntryExampleContent {
  example = Array(new EntryValueItem());
}

export interface EntrySense {
  definitionOrGloss: EntryValue[];
  examplesContents?: EntryExampleContent[];
  semanticDomains?: EntryValue[];
  guid?: string;
}

export class EntrySenseItem implements EntrySense {
  definitionOrGloss = Array(new EntryValueItem());

  examplesContents? = Array(new EntryExampleContentItem());

  semanticDomains? = Array(new EntryValueItem());

  guid? = '';
}

export interface DictionaryEntry {
  _id: string;
  dictionaryId: string;
  letterHead: string;
  mainHeadWord: EntryValue[];
  senses: EntrySense[];
  reversalLetterHeads: EntryValue[];
  pronunciations?: EntryValue[];
  morphoSyntaxAnalysis?: EntryAnalysis;
  audio: EntryFile;
  pictures: EntryFile[];
  updatedAt?: string;
}

export class DictionaryEntryItem implements DictionaryEntry {
  _id: string;

  dictionaryId: string;

  letterHead: string;

  mainHeadWord: EntryValueItem[];

  senses: EntrySenseItem[];

  reversalLetterHeads: EntryValueItem[];

  pronunciations: EntryValueItem[];

  morphoSyntaxAnalysis: EntryAnalysisItem;

  audio: EntryFileItem;

  pictures: EntryFileItem[];

  updatedAt: string;

  constructor(guid: string, dictionaryId: string, updatedAt?: string) {
    this._id = guid;
    this.dictionaryId = dictionaryId;
    this.updatedAt = updatedAt ?? new Date().toUTCString();
  }
}

export interface Language {
  lang: string;
  title: string;
  letters: string[];
  partsOfSpeech?: string[];
  cssFiles?: string[];
  entriesCount?: number;
}

export class LanguageItem implements Language {
  lang = '';

  title = '';

  letters: string[] = [];

  partsOfSpeech?: string[] = [];

  cssFiles?: string[] = [];
}

export interface Dictionary {
  _id: string;
  mainLanguage: Language;
  reversalLanguages: Language[];
  semanticDomains?: EntryValue[];
  updatedAt: string;
}

export class DictionaryItem implements Dictionary {
  _id: string;

  mainLanguage: LanguageItem;

  reversalLanguages: LanguageItem[];

  semanticDomains?: EntryValueItem[];

  updatedAt: string;

  constructor(dictionaryId: string) {
    this._id = dictionaryId;
    this.updatedAt = new Date().toUTCString();
  }
}

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
