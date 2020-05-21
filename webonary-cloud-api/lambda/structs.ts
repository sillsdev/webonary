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

export interface EntryListOption {
  lang: string;
  abbreviation: string;
  name: string;
  nameInsensitive?: string; // lowercase and normalized
  guid?: string;
}

export class EntryListOptionItem implements EntryListOption {
  lang = '';

  abbreviation = '';

  name = '';

  nameInsensitive? = '';

  guid? = '';
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

export interface EntrySemanticDomain {
  abbreviation: EntryValue[];
  name: EntryValue[];
}

export class EntrySemanticDomainItem implements EntrySemanticDomain {
  abbreviation = Array(new EntryValueItem());

  name = Array(new EntryValueItem());
}

export interface EntrySense {
  definitionOrGloss: EntryValue[];
  examplesContents?: EntryExampleContent[];
  semanticDomains?: EntrySemanticDomain[];
  guid?: string;
}

export class EntrySenseItem implements EntrySense {
  definitionOrGloss = Array(new EntryValueItem());

  examplesContents? = Array(new EntryExampleContentItem());

  semanticDomains? = Array(new EntrySemanticDomainItem());

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
  cssFiles?: string[];
  entriesCount?: number;
}

export class LanguageItem implements Language {
  lang = '';

  title = '';

  letters: string[] = [];

  cssFiles?: string[] = [];
}

export interface Dictionary {
  _id: string;
  updatedAt: string;
  mainLanguage: Language;
  reversalLanguages: Language[];
  partsOfSpeech?: EntryListOption[];
  semanticDomains?: EntryListOption[];
}

export class DictionaryItem implements Dictionary {
  _id: string;

  updatedAt: string;

  mainLanguage: LanguageItem;

  reversalLanguages: LanguageItem[];

  partsOfSpeech?: EntryListOptionItem[];

  semanticDomains?: EntryListOptionItem[];

  constructor(dictionaryId: string, updatedAt?: string) {
    this._id = dictionaryId;
    this.updatedAt = updatedAt ?? new Date().toUTCString();

    // Set initial values so we can do Object.keys for dynamic case-insensitive copying
    this.mainLanguage = new LanguageItem();
    this.reversalLanguages = Array(new LanguageItem());
    this.partsOfSpeech = Array(new EntryListOptionItem());
    this.semanticDomains = Array(new EntryListOptionItem());
  }
}

export interface DbFindParameters {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  [key: string]: any;
}

export enum DbPaths {
  SEM_DOMS_LANG = 'semanticDomains.lang',
  SEM_DOMS_ABBREV = 'semanticDomains.abbrev',
  SEM_DOMS_VALUE = 'semanticDomains.value',
  SEM_DOMS_VALUE_INSENSITIVE = 'semanticDomains.valueInsensitive',
  ENTRY_MAIN_HEADWORD_LANG = 'mainHeadWord.lang',
  ENTRY_MAIN_HEADWORD_VALUE = 'mainHeadWord.value',
  ENTRY_SENSES = 'senses',
  ENTRY_DEFINITION = 'senses.definitionOrGloss',
  ENTRY_DEFINITION_LANG = 'senses.definitionOrGloss.lang',
  ENTRY_DEFINITION_VALUE = 'senses.definitionOrGloss.value',
  ENTRY_PART_OF_SPEECH_VALUE = 'morphoSyntaxAnalysis.partOfSpeech.value',
  ENTRY_SEM_DOMS_ABBREV = 'senses.semanticDomains.abbreviation',
  ENTRY_SEM_DOMS_ABBREV_VALUE = 'senses.semanticDomains.abbreviation.value',
  ENTRY_SEM_DOMS_NAME_VALUE = 'senses.semanticDomains.name.value',
}
