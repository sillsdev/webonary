/* eslint-disable array-callback-return */
import { load } from 'cheerio';
import {
  Entry,
  ReversalEntry,
  ReversalSense,
  EntryValue,
  ENTRY_TYPE_REVERSAL,
} from 'lambda/entry.model';
import { Options, FlexXhtmlParser } from './flexXhtmlParser';

export class FlexXhtmlParserReversal extends FlexXhtmlParser {
  public parsedReversalEntries: ReversalEntry[];

  public constructor(toBeParsed: string, options: Partial<Options> = {}) {
    super(toBeParsed, { ...options, entryClass: ENTRY_TYPE_REVERSAL });

    this.parsedReversalEntries = this.parsedEntries.map((entry) =>
      FlexXhtmlParserReversal.parseReversalEntry(entry),
    );
  }

  public static parseReversalEntry(entry: Entry): ReversalEntry {
    const $ = load(entry.displayXhtml);

    const reversalform: EntryValue[] = [];
    $('span.reversalform span').map((i, elem) => {
      const lang = $(elem).attr('lang');
      const value = $(elem).text();
      if (lang && value) {
        reversalform.push({ lang, value });
      }
    });

    const sensesrs: ReversalSense[] = [];
    $('span.sensesr').map((i, elem) => {
      const guid = $(elem).attr('entryguid');
      if (guid) {
        const headword: EntryValue[] = [];
        $('span.sensesr span.headword span').map((childIndex, childElem) => {
          const lang = $(childElem).attr('lang');
          const value = $(childElem).text();
          if (lang && value) {
            headword.push({ lang, value });
          }
        });

        const partofspeech: EntryValue[] = [];
        $('span.morphosyntaxanalysis span.mlpartofspeech span').map((childIndex, childElem) => {
          const lang = $(childElem).attr('lang');
          const value = $(childElem).text();
          if (lang && value) {
            partofspeech.push({ lang, value });
          }
        });

        sensesrs.push({ guid, headword, partofspeech });
      }
    });

    const reversalEntry: ReversalEntry = {
      ...entry,
      reversalform,
      sensesrs,
    };

    return reversalEntry;
  }
}

export default FlexXhtmlParserReversal;
