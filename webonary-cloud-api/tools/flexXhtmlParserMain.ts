/* eslint-disable array-callback-return */
import * as cheerio from 'cheerio';
import { Options, FlexXhtmlParser } from './flexXhtmlParser';
import {
  DictionaryEntry,
  Entry,
  EntryFile,
  EntryValue,
  EntrySemanticDomain,
  ENTRY_TYPE_MAIN,
} from '../lambda/entry.model';
import { DictionaryItem, ListOption } from '../lambda/dictionary.model';

export class FlexXhtmlParserMain extends FlexXhtmlParser {
  public parsedFonts: Map<string, string>;

  public parsedLanguages: Map<string, string>;

  public parsedDictionaryEntries: DictionaryEntry[];

  public constructor(toBeParsed: string, options: Partial<Options> = {}) {
    super(toBeParsed, { ...options, entryClass: ENTRY_TYPE_MAIN });

    this.parsedDictionaryEntries = this.parsedEntries.map(entry =>
      FlexXhtmlParserMain.parseDictionaryEntry(entry),
    );

    this.parsedFonts = new Map();
    this.parsedLanguages = new Map();

    this.parseHead();
  }

  protected parseHead(): void {
    const startIndex = this.toBeParsed.indexOf('<head>');
    const endIndex = this.toBeParsed.indexOf('</head>');
    const $ = cheerio.load(this.toBeParsed.substring(startIndex + 6, endIndex));

    $('meta').map((i, elem) => {
      const name = $(elem).attr('name');
      const content = $(elem).attr('content');
      if (name && content) {
        if (name === 'DC.language') {
          const [languageCode, languageName] = content.split(':');
          if (languageCode && languageName) {
            this.parsedLanguages.set(languageCode, languageName);
          }
        } else {
          this.parsedFonts.set(name, content);
        }
      }
    });
  }

  public static parseDictionaryEntry(entry: Entry): DictionaryEntry {
    const $ = cheerio.load(entry.displayXhtml);

    const mainHeadWord: EntryValue[] = [];
    $('span.mainheadword span a').map((i, elem) => {
      const lang =
        $(elem)
          .parent()
          .attr('lang') ?? '';
      const value = $(elem).text();
      mainHeadWord.push({ lang, value });
    });

    const audioId = $('audio').attr('id') ?? '';
    const audioSrc =
      $('audio > source')
        .attr('src')
        ?.replace(/\\/g, '/') ?? '';
    const fileClass = $('audio + a').attr('class') ?? '';
    const audio: EntryFile = { id: audioId, src: audioSrc, fileClass };

    // TODO: There can be multiple media files, e.g. Hayashi, one for lexemeform and another in pronunciations
    const pictures: EntryFile[] = [];
    $('span.pictures div.picture img').map((i, elem) => {
      const id = $(elem).attr('id') ?? '';
      const src =
        $(elem)
          .attr('src')
          ?.replace(/\\/g, '/') ?? '';
      const caption =
        $(elem)
          .next()
          .find('span.headword span')
          .text() ?? '';
      pictures.push({ id, src, caption });
    });

    const pronunciations: EntryValue[] = [];
    $('span.pronunciations span.pronunciation span span').map((i, elem) => {
      const key =
        $(elem)
          .parent()
          .attr('class') ?? '';
      const lang = $(elem).attr('lang') ?? '';
      const value = $(elem).text();
      pronunciations.push({ lang, value, key });
    });

    const partOfSpeech: EntryValue[] = [];
    $('span.morphosyntaxanalysis span.partofspeech span').map((i, elem) => {
      const lang = $(elem).attr('lang');
      const value = $(elem).text();
      if (lang && value) {
        partOfSpeech.push({ lang, value });
      }
    });

    const definitionOrGloss: EntryValue[] = [];
    $('span.definitionorgloss span').map((i, elem) => {
      const lang = $(elem).attr('lang');
      const value = $(elem).text();
      if (lang && value) {
        // TODO: this was in moore, but necessary?
        // if ($(elem).prev().hasClass('writingsystemprefix')) {
        definitionOrGloss.push({ lang, value });
      }
    });

    const semanticDomains: EntrySemanticDomain[] = [];
    $('span.semanticdomain span.name span').map((i, elem) => {
      const lang = $(elem).attr('lang');
      const name = $(elem).text();
      if (lang && name) {
        const abbreviation = $(elem)
          .parent()
          .parent()
          .children('span.abbreviation')
          .children()
          .text();

        semanticDomains.push({
          abbreviation: [{ lang, value: abbreviation }],
          name: [{ lang, value: name }],
        });
      }
    });

    const reversalLetterHeads = definitionOrGloss.reduce(
      (letterHeads: EntryValue[], definitionEntry: EntryValue): EntryValue[] => {
        const { lang } = definitionEntry;
        const value = definitionEntry.value.toLowerCase().substring(0, 1);
        if (!letterHeads.some(letter => letter.lang === lang && letter.value === value)) {
          letterHeads.push({ lang, value });
        }
        return letterHeads;
      },
      [],
    );

    return {
      ...entry,
      mainHeadWord,
      pronunciations,
      senses: [{ definitionOrGloss, semanticDomains }],
      reversalLetterHeads,
      morphoSyntaxAnalysis: { partOfSpeech },
      audio,
      pictures,
    };
  }

  public getDictionaryData(): DictionaryItem | undefined {
    const _id = this.options.dictionaryId;
    const loadDictionary = new DictionaryItem(_id);

    if (_id && this.parsedDictionaryEntries.length) {
      // We can safely assume that all the entries have same main language
      const mainLang = this.parsedDictionaryEntries[0].mainHeadWord[0].lang ?? '';
      loadDictionary.mainLanguage = {
        lang: mainLang,
        title: this.parsedLanguages.get(mainLang) ?? '',
        letters: this.parsedLetters,
        cssFiles: [],
      };

      loadDictionary.partsOfSpeech = this.parsedDictionaryEntries.reduce(
        (partsOfSpeech: ListOption[], entry) => {
          const newDictionaryPartsOfSpeech = partsOfSpeech;
          if (entry.morphoSyntaxAnalysis) {
            entry.morphoSyntaxAnalysis.partOfSpeech.forEach(entryPartOfSpeech => {
              if (
                entryPartOfSpeech.lang !== '' &&
                entryPartOfSpeech.value !== '' &&
                !newDictionaryPartsOfSpeech.find(
                  item =>
                    item.lang === entryPartOfSpeech.lang &&
                    item.abbreviation === entryPartOfSpeech.value &&
                    item.name === entryPartOfSpeech.value,
                )
              ) {
                newDictionaryPartsOfSpeech.push({
                  abbreviation: entryPartOfSpeech.value,
                  lang: entryPartOfSpeech.lang,
                  name: entryPartOfSpeech.value,
                });
              }
            });
          }
          return newDictionaryPartsOfSpeech;
        },
        [],
      );

      loadDictionary.semanticDomains = this.parsedDictionaryEntries.reduce(
        (semDoms: ListOption[], entry) => {
          const newDictionarySemDoms = semDoms;
          entry.senses.forEach(sense => {
            if (sense.semanticDomains && sense.semanticDomains.length) {
              sense.semanticDomains.forEach(semDom => {
                semDom.abbreviation.forEach(abbreviation => {
                  const matchingName = semDom.name.find(item => item.lang === abbreviation.lang);
                  if (matchingName) {
                    const foundAbbreviation = newDictionarySemDoms.find(
                      item =>
                        item.abbreviation === abbreviation.value &&
                        item.lang === abbreviation.lang &&
                        item.name === matchingName.value,
                    );
                    if (!foundAbbreviation || foundAbbreviation.name !== matchingName.value) {
                      newDictionarySemDoms.push({
                        abbreviation: abbreviation.value,
                        lang: matchingName.lang,
                        name: matchingName.value,
                      });
                    }
                  }
                });
              });
            }
          });
          return newDictionarySemDoms;
        },
        [],
      );
    }
    return loadDictionary;
  }
}

export default FlexXhtmlParserMain;
