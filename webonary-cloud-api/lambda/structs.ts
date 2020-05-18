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

    // Set initial values so we can do Object.keys for dynamic case-insensitive copying
    this.letterHead = '';
    this.mainHeadWord = Array(new EntryValueItem());
    this.senses = Array(new EntrySenseItem());
    this.reversalLetterHeads = Array(new EntryValueItem());
    this.pronunciations = Array(new EntryValueItem());
    this.morphoSyntaxAnalysis = new EntryAnalysisItem();
    this.audio = new EntryFileItem();
    this.pictures = Array(new EntryFileItem());
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
  updatedAt: string;
  mainLanguage: Language;
  reversalLanguages: Language[];
  semanticDomains?: EntryValue[];
}

export class DictionaryItem implements Dictionary {
  _id: string;

  updatedAt: string;

  mainLanguage: LanguageItem;

  reversalLanguages: LanguageItem[];

  semanticDomains?: EntryValueItem[];

  constructor(dictionaryId: string, updatedAt?: string) {
    this._id = dictionaryId;
    this.updatedAt = updatedAt ?? new Date().toUTCString();

    // Set initial values so we can do Object.keys for dynamic case-insensitive copying
    this.mainLanguage = new LanguageItem();
    this.reversalLanguages = Array(new LanguageItem());
    this.semanticDomains = Array(new EntryValueItem());
  }
}

export interface DbFindParameters {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  [key: string]: any;
}

export enum DbPaths {
  SEM_DOMS_KEY = 'semanticDomains.key',
  SEM_DOMS_LANG = 'semanticDomains.lang',
  SEM_DOMS_VALUE = 'semanticDomains.value',
  SEM_DOMS_SEARCH_VALUE = 'semanticDomains.searchValue',
  ENTRY_MAIN_HEADWORD_LANG = 'mainHeadWord.0.lang',
  ENTRY_MAIN_HEADWORD_VALUE = 'mainHeadWord.0.value',
  ENTRY_DEFINITION = 'senses.definitionOrGloss',
  ENTRY_DEFINITION_LANG = 'senses.definitionOrGloss.lang',
  ENTRY_DEFINITION_VALUE = 'senses.definitionOrGloss.value',
  ENTRY_PART_OF_SPEECH_VALUE = 'morphoSyntaxAnalysis.partOfSpeech.value',
  ENTRY_SEM_DOMS_VALUE = 'senses.semanticDomains.value',
}
