/* eslint-disable max-classes-per-file */
export interface Language {
  lang: string;
  title: string;
  letters: string[];
  cssFiles?: string[];
  entriesCount?: number;
}

export declare class LanguageItem implements Language {
  lang: string;

  title: string;

  letters: string[];

  cssFiles?: string[];
}

export interface ListOption {
  lang: string;
  abbreviation: string;
  name: string;
  nameInsensitive?: string; // lowercase and normalized
  guid?: string;
}

export class ListOptionItem implements ListOption {
  lang = '';

  abbreviation = '';

  name = '';

  nameInsensitive? = '';

  guid? = '';
}

export interface Dictionary {
  _id: string;
  updatedAt: string;
  mainLanguage: Language;
  reversalLanguages: Language[];
  partsOfSpeech?: ListOption[];
  semanticDomains?: ListOption[];
}
export declare class DictionaryItem implements Dictionary {
  _id: string;

  updatedAt: string;

  mainLanguage: LanguageItem;

  reversalLanguages: LanguageItem[];

  partsOfSpeech?: ListOptionItem[];

  semanticDomains?: ListOptionItem[];

  constructor(dictionaryId: string, updatedAt?: string);
}

export declare enum DbPaths {
  SEM_DOMS_LANG = 'semanticDomains.lang',
  SEM_DOMS_ABBREV = 'semanticDomains.abbrev',
  SEM_DOMS_VALUE = 'semanticDomains.value',
  SEM_DOMS_VALUE_INSENSITIVE = 'semanticDomains.valueInsensitive',
}
