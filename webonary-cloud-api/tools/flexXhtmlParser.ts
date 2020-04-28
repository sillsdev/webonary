/* eslint-disable array-callback-return */
import * as cheerio from 'cheerio';
import { EntryFile, EntryValue, EntryData, LoadEntry } from '../lambda/db';

export interface Options {
  dictionary: string;
}

export class FlexXhtmlParser {
  protected options: Options;

  protected toBeParsed: string;

  public parsedItems: LoadEntry[];

  public constructor(toBeParsed: string, options: Partial<Options> = {}) {
    this.toBeParsed = toBeParsed;

    this.options = Object.assign(options);

    this.parsedItems = [];
  }

  public async parse(): Promise<void> {
    const matches =
      this.toBeParsed.replace(/\r?\n|\r/g, '').match(/<div class="entry" (.+?)<\/div>/gm) ?? [];

    this.parsedItems = matches.map(
      (entryData): LoadEntry => {
        return FlexXhtmlParser.parseEntry(this.options.dictionary, entryData);
      },
    );
  }

  static parseEntry(dictionary: string, entryData: string): LoadEntry {
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
      const type =
        $(elem)
          .parent()
          .attr('class') ?? '';
      const lang = $(elem).attr('lang') ?? '';
      const value = $(elem).text();
      pronunciations.push({ lang, value, type });
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

    const reverseLetterHeads = definitionOrGloss.reduce(
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
      dictionary,
      mainHeadWord,
      letterHead,
      pronunciations,
      senses: { partOfSpeech, definitionOrGloss },
      reverseLetterHeads,
      audio,
      pictures,
    };

    return { guid, data };
  }
}
