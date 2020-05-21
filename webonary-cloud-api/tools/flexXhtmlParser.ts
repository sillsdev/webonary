/* eslint-disable array-callback-return */
import * as cheerio from 'cheerio';
import { DictionaryEntry, EntryFile, EntryValue, EntrySemanticDomain } from '../lambda/entry.model';
import { DictionaryItem, ListOption } from '../lambda/dictionary.model';

export interface Options {
  dictionaryId: string;
}

export class FlexXhtmlParser {
  protected options: Options;

  protected toBeParsed: string;

  public parsedFonts: Map<string, string>;

  public parsedLanguages: Map<string, string>;

  public parsedEntries: DictionaryEntry[];

  public constructor(toBeParsed: string, options: Partial<Options> = {}) {
    this.toBeParsed = toBeParsed;

    this.options = Object.assign(options);

    this.parsedFonts = new Map();
    this.parsedLanguages = new Map();
    this.parsedEntries = [];

    this.parseHead();
    this.parseBody();
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

  protected parseBody(): void {
    const matches =
      this.toBeParsed.replace(/\r?\n|\r/g, '').match(/<div class="entry" (.+?)<\/div>/gm) ?? [];

    this.parsedEntries = matches.map(
      (entryData): DictionaryEntry => {
        return FlexXhtmlParser.parseEntry(this.options.dictionaryId, entryData);
      },
    );
  }

  public static parseEntry(dictionaryId: string, entryData: string): DictionaryEntry {
    const $ = cheerio.load(entryData);

    // NOTE: guid field in Webonary and FLex actually includes the character 'g' at the beginning
    const _id =
      $('div.entry')
        .attr('id')
        ?.substring(1) ?? '';

    const mainHeadWord: EntryValue[] = [];
    $('span.mainheadword span a').map((i, elem) => {
      const lang =
        $(elem)
          .parent()
          .attr('lang') ?? '';
      const value = $(elem).text();
      mainHeadWord.push({ lang, value });
    });

    // TODO: I think current versions of FLex allows only one mainHeadWord. Need to confirm.
    const letterHead = mainHeadWord.length
      ? mainHeadWord[0].value.toLowerCase().substring(0, 1)
      : '';
    /*
    <span class="lexemeform">
    <span>
        <audio id="g636928554709180064rɩɩb_bʋkr_so-tũudga">
            <source src="AudioVisual\\636928554709180064rɩɩb bʋkr so-tũudga.mp3"/>
        </audio>
        <a class="mos-Zxxx-x-audio" href="#g636928554709180064rɩɩb_bʋkr_so-tũudga" onclick="document.getElementById('g636928554709180064rɩɩb_bʋkr_so-tũudga').play()"/>
    </span>
    </span>
    */

    const audioId = $('audio').attr('id') ?? '';
    const audioSrc =
      $('audio > source')
        .attr('src')
        ?.replace(/\\/g, '/') ?? '';
    const fileClass = $('audio + a').attr('class') ?? '';
    const audio: EntryFile = { id: audioId, src: audioSrc, fileClass };

    // TODO: There can be multiple media files, e.g. Hayashi, one for lexemeform and another in pronunciations

    /* 
    <span class="pictures">
    <div class="picture">
        <img class="thumbnail" id="g9f0ee134-8268-45b1-ae6d-4bb7aac97920" src="pictures\tube_digestif.jpg"/>
        <div class="captionContent">
            <span class="headword">
                <span lang="mos">rɩɩb bʋkr so-tũudga</span>
            </span>
        </div>
    </div>
    </span>
    */

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

    const partOfSpeech: EntryValue = {
      lang:
        $(
          'span.senses span.sharedgrammaticalinfo span.morphosyntaxanalysis span.partofspeech span',
        ).attr('lang') ?? '',
      value: $(
        'span.senses span.sharedgrammaticalinfo span.morphosyntaxanalysis span.partofspeech span',
      ).text(),
    };

    const definitionOrGloss: EntryValue[] = [];
    $('span.senses span.sensecontent span.sense span.definitionorgloss span').map((i, elem) => {
      const lang = $(elem).attr('lang');
      const value = $(elem).text();
      if (lang && value) {
        // TODO: this was in moore, but necessary?
        // if ($(elem).prev().hasClass('writingsystemprefix')) {
        definitionOrGloss.push({ lang, value });
      }
    });

    const semanticDomains: EntrySemanticDomain[] = [];
    $(
      'span.senses span.sensecontent span.sense span.semanticdomains span.semanticdomain span.name span',
    ).map((i, elem) => {
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
      (letterHeads: EntryValue[], entry: EntryValue): EntryValue[] => {
        const { lang } = entry;
        const value = entry.value.toLowerCase().substring(0, 1);
        if (!letterHeads.some(letter => letter.lang === lang && letter.value === value)) {
          letterHeads.push({ lang, value });
        }
        return letterHeads;
      },
      [],
    );

    const entry: DictionaryEntry = {
      _id,
      dictionaryId,
      mainHeadWord,
      letterHead,
      pronunciations,
      senses: [{ definitionOrGloss, semanticDomains }],
      reversalLetterHeads,
      morphoSyntaxAnalysis: { partOfSpeech: [partOfSpeech] },
      audio,
      pictures,
    };

    return entry;
  }

  public getDictionaryData(): DictionaryItem | undefined {
    const _id = this.options.dictionaryId;

    const loadDictionary = new DictionaryItem(_id);

    if (_id && this.parsedEntries.length) {
      // We can safely assume that all the entries have same main language
      const mainLang = this.parsedEntries[0].mainHeadWord[0].lang;
      const mainLetters = this.parsedEntries
        .map(entry => entry.letterHead)
        .filter((item, index, items) => items.indexOf(item) === index)
        .sort((a, b) => a.localeCompare(b));

      loadDictionary.mainLanguage = {
        lang: mainLang,
        title: this.parsedLanguages.get(mainLang) ?? '',
        letters: mainLetters,
      };

      const reversalLetterHeads = this.parsedEntries.reduce((letterHeads, entry) => {
        const newLetterHeads = letterHeads;
        entry.reversalLetterHeads.forEach(head => {
          const langLetters = newLetterHeads.get(head.lang);
          if (langLetters) {
            if (!langLetters.find(letter => letter === head.value)) {
              langLetters.push(head.value);
              newLetterHeads.set(head.lang, langLetters);
            }
          } else {
            newLetterHeads.set(head.lang, [head.value]);
          }
        });
        return newLetterHeads;
      }, new Map<string, string[]>());

      loadDictionary.reversalLanguages = [];
      reversalLetterHeads.forEach((letters, lang) =>
        loadDictionary.reversalLanguages.push({
          lang,
          title: this.parsedLanguages.get(lang) ?? '',
          letters: letters.sort((a, b) => a.localeCompare(b)),
        }),
      );

      loadDictionary.partsOfSpeech = this.parsedEntries.reduce(
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

      loadDictionary.semanticDomains = this.parsedEntries.reduce((semDoms: ListOption[], entry) => {
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
      }, []);
    }
    return loadDictionary;
  }
}
