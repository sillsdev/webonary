/* eslint-disable array-callback-return */
import * as cheerio from 'cheerio';
import {
  Entry,
  ReversalEntry,
  ReversalSense,
  EntryValue,
  ENTRY_TYPE_REVERSAL,
} from '../lambda/entry.model';
import { Options, FlexXhtmlParser } from './flexXhtmlParser';

export class FlexXhtmlParserReversal extends FlexXhtmlParser {
  public parsedReversalEntries: ReversalEntry[];

  public constructor(toBeParsed: string, options: Partial<Options> = {}) {
    super(toBeParsed, { ...options, entryClass: ENTRY_TYPE_REVERSAL });

    this.parsedReversalEntries = this.parsedEntries.map(entry =>
      FlexXhtmlParserReversal.parseReversalEntry(entry),
    );
  }

  public static parseReversalEntry(entry: Entry): ReversalEntry {
    const $ = cheerio.load(entry.displayXhtml);

    const reversalForm: EntryValue[] = [];
    $('span.reversalform span').map((i, elem) => {
      const lang = $(elem).attr('lang');
      const value = $(elem).text();
      if (lang && value) {
        reversalForm.push({ lang, value });
      }
    });

    const sensesRs: ReversalSense[] = [];
    $('span.sensesr').map((i, elem) => {
      const guid = $(elem).attr('entryguid');
      if (guid) {
        const headWord: EntryValue[] = [];
        $('span.sensesr span.headword span').map((childIndex, childElem) => {
          const lang = $(childElem).attr('lang');
          const value = $(childElem).text();
          if (lang && value) {
            headWord.push({ lang, value });
          }
        });

        const partOfSpeech: EntryValue[] = [];
        $('span.morphosyntaxanalysis span.mlpartofspeech span').map((childIndex, childElem) => {
          const lang = $(childElem).attr('lang');
          const value = $(childElem).text();
          if (lang && value) {
            partOfSpeech.push({ lang, value });
          }
        });

        sensesRs.push({ guid, headWord, partOfSpeech });
      }
    });

    const reversalEntry: ReversalEntry = {
      ...entry,
      reversalForm,
      sensesRs,
    };

    return reversalEntry;
  }
}

export default FlexXhtmlParserReversal;
