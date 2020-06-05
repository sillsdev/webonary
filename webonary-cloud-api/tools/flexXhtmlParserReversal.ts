/* eslint-disable array-callback-return */
import * as cheerio from 'cheerio';
import { Options, FlexXhtmlParser } from './flexXhtmlParser';

import { Entry, ReversalEntry, EntryValue, ENTRY_TYPE_REVERSAL } from '../lambda/entry.model';

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

    const lang = $('span.reversalform span').attr('lang') ?? '';
    const value = $('span.reversalform span').text();

    const reversalForm: EntryValue = { lang, value };

    return { ...entry, reversalForm };
  }
}

export default FlexXhtmlParserReversal;
