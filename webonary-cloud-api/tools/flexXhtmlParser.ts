/* eslint-disable array-callback-return */
import * as cheerio from 'cheerio';
import {
  EntryFile,
  EntryValue,
  EntryData,
  PostEntry,
  PostDictionary,
  DictionaryLanguage,
} from '../lambda/db';

export interface Options {
  dictionaryId: string;
}

export class FlexXhtmlParser {
  protected options: Options;

  protected toBeParsed: string;

  public parsedFonts: Map<string, string>;

  public parsedLanguages: Map<string, string>;

  public parsedEntries: PostEntry[];

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
      (entryData): PostEntry => {
        return FlexXhtmlParser.parseEntry(this.options.dictionaryId, entryData);
      },
    );
  }

  public static parseEntry(dictionaryId: string, entryData: string): PostEntry {
    const $ = cheerio.load(entryData);

    // NOTE: guid field in Webonary and FLex actually includes the character 'g' at the beginning
    const guid =
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

    const semanticDomains: EntryValue[] = [];
    $(
      'span.senses span.sensecontent span.sense span.semanticdomains span.semanticdomain span.name span',
    ).map((i, elem) => {
      const lang = $(elem).attr('lang');
      const value = $(elem).text();
      if (lang && value) {
        const key = $(elem)
          .parent()
          .parent()
          .children('span.abbreviation')
          .children()
          .text();
        semanticDomains.push({ key, lang, value });
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

    const data: EntryData = {
      dictionaryId,
      mainHeadWord,
      letterHead,
      pronunciations,
      senses: { partOfSpeech, definitionOrGloss, semanticDomains },
      reversalLetterHeads,
      audio,
      pictures,
    };

    return { guid, data };
  }

  public getDictionaryData(): PostDictionary | undefined {
    const id = this.options.dictionaryId;

    let loadDictionary;
    if (id && this.parsedEntries.length) {
      // We can safely assume that all the entries have same main language
      const mainLang = this.parsedEntries[0].data.mainHeadWord[0].lang;
      const mainLetters = this.parsedEntries
        .map(entry => entry.data.letterHead)
        .filter((item, index, items) => items.indexOf(item) === index)
        .sort((a, b) => a.localeCompare(b));

      const mainLanguage: DictionaryLanguage = {
        lang: mainLang,
        title: this.parsedLanguages.get(mainLang),
        letters: mainLetters,
      };

      const reversalLetterHeads = this.parsedEntries.reduce((letterHeads, entry) => {
        const newLetterHeads = letterHeads;
        entry.data.reversalLetterHeads.forEach(head => {
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

      const reversalLanguages: DictionaryLanguage[] = [];
      reversalLetterHeads.forEach((letters, lang) =>
        reversalLanguages.push({
          lang,
          title: this.parsedLanguages.get(lang),
          letters: letters.sort((a, b) => a.localeCompare(b)),
        }),
      );

      /*
      const semanticDomains = this.parsedEntries.reduce((semDoms, entry) => {
        const newSemDoms = semDoms;
        if (entry.data.senses.semanticDomains) {
          entry.data.senses.semanticDomains.forEach(semDom => {
            const semDomsForLang = newSemDoms.get(semDom.lang);
            if (semDomsForLang) {
              if (
                !semDomsForLang.find(item => item.key === semDom.key && item.value === semDom.value)
              ) {
                semDomsForLang.push(semDom);
                newSemDoms.set(semDom.lang, semDomsForLang);
              }
            } else {
              newSemDoms.set(semDom.lang, [semDom]);
            }
          });
        }
        return newSemDoms;
      }, new Map<string, EntryValue[]>());
      */

      const semanticDomains = this.parsedEntries.reduce((semDoms: EntryValue[], entry) => {
        const newSemDoms = semDoms;
        if (entry.data.senses.semanticDomains) {
          entry.data.senses.semanticDomains.forEach(semDom => {
            if (
              !newSemDoms.find(
                item =>
                  item.key === semDom.key &&
                  item.lang === semDom.lang &&
                  item.value === semDom.value,
              )
            ) {
              newSemDoms.push(semDom);
            }
          });
        }
        return newSemDoms;
      }, []);

      loadDictionary = {
        id,
        data: {
          mainLanguage,
          reversalLanguages,
          semanticDomains,
        },
      };
    }

    return loadDictionary;
  }
}
