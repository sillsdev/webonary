/* eslint-disable max-classes-per-file */
// eslint-disable-next-line @typescript-eslint/no-explicit-any
export type EntryType = Record<string, any> & {
  _id: string;
  guid: string;
  dictionaryId: string;
  updatedAt?: Date;
  updatedBy?: string;
};

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
  partofspeech: EntryValue[];
}

export class EntryAnalysisItem implements EntryAnalysis {
  partofspeech = Array(new EntryValueItem());
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
  definitionorgloss: EntryValue[];
  examplescontents?: EntryExampleContent[];
  semanticdomains?: EntrySemanticDomain[];
  guid?: string;
}

export class EntrySenseItem implements EntrySense {
  definitionorgloss = Array(new EntryValueItem());

  examplescontents? = Array(new EntryExampleContentItem());

  semanticdomains? = Array(new EntrySemanticDomainItem());

  guid? = '';
}

export interface Entry {
  _id?: string;
  guid: string;
  dictionaryId: string;
  letterHead: string;
  sortIndex: number;
  displayXhtml: string;
  updatedAt?: Date;
  updatedBy?: string;
}

export class EntryItem implements Entry {
  _id: string;

  guid: string;

  dictionaryId: string;

  letterHead: string;

  sortIndex: number;

  displayXhtml: string;

  // This is a copy of displayXhtml with all the HTML stripped. This is used for full text search.
  displayText: string;

  updatedAt?: Date;

  updatedBy?: string;

  constructor(guid: string, dictionaryId: string, updatedBy?: string, updatedAt?: Date) {
    this._id = `${dictionaryId}::${guid}`;
    this.guid = guid;
    this.dictionaryId = dictionaryId;
    this.letterHead = '';
    this.sortIndex = 0;
    this.displayXhtml = '';
    this.updatedBy = updatedBy ?? '';
    this.updatedAt = updatedAt ?? new Date();
  }
}

export interface DictionaryEntry extends Entry {
  mainheadword: EntryValue[];
  senses: EntrySense[];
  reversalLetterHeads: EntryValue[];
  pronunciations?: EntryValue[];
  morphosyntaxanalysis?: EntryAnalysis;
  audio: EntryFile;
  pictures: EntryFile[];
}

export class DictionaryEntryItem extends EntryItem {
  mainheadword: EntryValueItem[];

  senses: EntrySenseItem[];

  reversalLetterHeads: EntryValueItem[];

  pronunciations: EntryValueItem[];

  morphosyntaxanalysis: EntryAnalysisItem;

  audio: EntryFileItem;

  pictures: EntryFileItem[];

  constructor(guid: string, dictionaryId: string, updatedBy?: string, updatedAt?: Date) {
    super(guid, dictionaryId, updatedBy, updatedAt);

    // Set initial values so we can do Object.keys for dynamic case-insensitive copying
    this.mainheadword = Array(new EntryValueItem());
    this.senses = Array(new EntrySenseItem());
    this.reversalLetterHeads = Array(new EntryValueItem());
    this.pronunciations = Array(new EntryValueItem());
    this.morphosyntaxanalysis = new EntryAnalysisItem();
    this.audio = new EntryFileItem();
    this.pictures = Array(new EntryFileItem());
  }
}

export interface ReversalSense {
  guid: string;

  headword: EntryValue[];

  partofspeech: EntryValue[];
}

export class ReversalSenseItem implements ReversalSense {
  guid = '';

  headword = Array(new EntryValueItem());

  partofspeech = Array(new EntryValueItem());
}

export interface ReversalEntry extends Entry {
  reversalform: EntryValue[];
  sensesrs: ReversalSense[];
}

export class ReversalEntryItem extends EntryItem {
  reversalform: EntryValueItem[];

  sensesrs: ReversalSenseItem[];

  constructor(guid: string, dictionaryId: string, updatedBy?: string, updatedAt?: Date) {
    super(guid, dictionaryId, updatedBy, updatedAt);

    // Set initial values so we can do Object.keys for dynamic case-insensitive copying
    this.letterHead = '';
    this.reversalform = Array(new EntryValueItem());
    this.sensesrs = Array(new ReversalSenseItem());
  }
}

export type EntryItemType = DictionaryEntryItem | ReversalEntryItem;

export const ENTRY_TYPE_MAIN = 'entry';
export const ENTRY_TYPE_REVERSAL = 'reversalindexentry';

export enum DbPaths {
  // dictionary entries
  ENTRY_MAIN_HEADWORD_LANG = 'mainheadword.lang',
  ENTRY_MAIN_HEADWORD_VALUE = 'mainheadword.value',
  ENTRY_MAIN_HEADWORD_FIRST_VALUE = 'mainheadword.0.value',
  ENTRY_MAIN_HEADWORD_SECOND_VALUE = 'mainheadword.1.value',

  ENTRY_HEADWORD_VALUE = 'headword.value', // minor entries
  ENTRY_CITATION_FORM_VALUE = 'citationform.value',
  ENTRY_LEXEME_FORM_VALUE = 'lexemeform.value',

  ENTRY_SENSES = 'senses',
  ENTRY_DEFINITION_OR_GLOSS = 'senses.definitionorgloss',
  ENTRY_DEFINITION_OR_GLOSS_LANG = 'senses.definitionorgloss.lang',
  ENTRY_DEFINITION_OR_GLOSS_VALUE = 'senses.definitionorgloss.value',
  ENTRY_DEFINITION_LANG = 'senses.definition.lang',
  ENTRY_DEFINITION_VALUE = 'senses.definition.value',
  ENTRY_GLOSS_LANG = 'senses.gloss.lang',
  ENTRY_GLOSS_VALUE = 'senses.gloss.value',

  ENTRY_EXAMPLE_VALUE = 'senses.examplescontents.example.value',
  ENTRY_EXAMPLE_TRANSLATION_VALUE = 'senses.examplescontents.translationcontents.translation.value',
  ENTRY_SCIENTIFIC_NAME_VALUE = 'senses.scientificname.value',

  ENTRY_SEM_DOMS_ABBREV = 'senses.semanticdomains.abbreviation',
  ENTRY_SEM_DOMS_ABBREV_VALUE = 'senses.semanticdomains.abbreviation.value',
  ENTRY_SEM_DOMS_NAME_VALUE = 'senses.semanticdomains.name.value',

  ENTRY_PART_OF_SPEECH = 'morphosyntaxanalysis.partofspeech',
  ENTRY_PART_OF_SPEECH_VALUE = 'morphosyntaxanalysis.partofspeech.value',
  ENTRY_GRAM_INFO_ABBREV = 'morphosyntaxanalysis.graminfoabbrev',
  ENTRY_GRAM_INFO_ABBREV_LANG = 'morphosyntaxanalysis.graminfoabbrev.lang',
  ENTRY_GRAM_INFO_ABBREV_VALUE = 'morphosyntaxanalysis.graminfoabbrev.value',

  ENTRY_LANG_TEXTS = 'langTexts',
  ENTRY_LANG_UNACCENTED_TEXTS = 'langUnaccentedTexts',
  ENTRY_SEARCH_TEXTS = 'searchTexts',

  // reversals
  ENTRY_REVERSAL_FORM_LANG = 'reversalform.lang',
  ENTRY_REVERSAL_FORM_FIRST_VALUE = 'reversalform.0.value',
  ENTRY_REVERSAL_FORM_SECOND_VALUE = 'reversalform.1.value',

  // common to dictionary entries and reversals
  DICTIONARY_ID = 'dictionaryId',
  LETTER_HEAD = 'letterHead',
  SORT_INDEX = 'sortIndex',
}
