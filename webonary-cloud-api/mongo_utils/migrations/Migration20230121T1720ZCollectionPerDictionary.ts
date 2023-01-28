/* eslint-disable @typescript-eslint/no-explicit-any */
/* eslint-disable import/no-relative-packages */
/* eslint-disable no-await-in-loop */
/* eslint-disable no-console */
import * as cheerio from 'cheerio';
import { Db } from 'mongodb';
import { MigrationInterface } from 'mongo-migrate-ts';

// use relative path so mongo-migrate cli can find it
import { DB_COLLECTION_ENTRIES, createEntriesIndexes, dbCollectionEntries } from '../../lambda/db';
import { DbPaths } from '../../lambda/entry.model';
import { transformToEntry } from '../../lambda/postEntry';

const logMessage = (message: string, previousTime?: number): void => {
  const currentTime = Date.now();
  const inSeconds = previousTime
    ? `in ${Math.floor((currentTime - previousTime) / 1000)} seconds`
    : '';
  console.log(`\n ${new Date(currentTime).toString()} ${message} ${inSeconds}`);
};

export class Migration20230121T1720ZCollectionPerDictionary implements MigrationInterface {
  parser: cheerio.Root;

  public async up(db: Db): Promise<any> {
    const dictionaryIds = await this.getLegacyDictionaryIds(db);

    // sequentially process to not overburden Mongo
    // eslint-disable-next-line no-restricted-syntax
    for (const dictionaryId of dictionaryIds) {
      logMessage(`Started migrating dictionary ${dictionaryId}... `);

      const resultCount = await this.migrateEntries(db, dictionaryId);
      logMessage(`Completed migrating ${resultCount} dictionary ${dictionaryId} entries.`);

      await createEntriesIndexes(db, dictionaryId);
      logMessage(`Created indexes for ${dictionaryId} entries.`);

      await db.collection(DB_COLLECTION_ENTRIES).deleteMany({ dictionaryId });
      logMessage(`Deleted legacy ${dictionaryId} entries.`);
    }
  }

  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  public async down(db: Db): Promise<any> {
    console.log('Nothing to undo here!');
  }

  async getLegacyDictionaryIds(db: Db): Promise<string[]> {
    const legacyCollection = db.collection(DB_COLLECTION_ENTRIES);
    return legacyCollection.distinct(DbPaths.DICTIONARY_ID);
  }

  async migrateEntries(db: Db, dictionaryId: string) {
    const entriesCollection = dbCollectionEntries(dictionaryId);
    const legacyEntries = await db
      .collection(DB_COLLECTION_ENTRIES)
      .find({ dictionaryId })
      .toArray();

    const resultPromises = legacyEntries.map(async (legacyEntry) => {
      const migratedEntry = transformToEntry({ postedEntry: legacyEntry, dictionaryId });

      // Step 1: Move renamed fields

      if (migratedEntry.mainHeadWord) {
        migratedEntry.mainheadword = migratedEntry.mainHeadWord;
        delete migratedEntry.mainHeadWord;
      }

      if (migratedEntry.morphoSyntaxAnalysis) {
        migratedEntry.morphosyntaxanalysis = {
          partofspeech: migratedEntry.morphoSyntaxAnalysis.partOfSpeech,
        };
        delete migratedEntry.morphoSyntaxAnalysis;
      }

      if (migratedEntry.senses && migratedEntry.senses.length) {
        migratedEntry.senses = migratedEntry.senses?.map(
          ({
            guid,
            definitionOrGloss,
            examplesContents,
            semanticDomains,
          }: {
            guid?: string;
            definitionOrGloss?: object[];
            examplesContents?: object[];
            semanticDomains?: object[];
          }) => {
            const migratedSense: Record<string, string | object[]> = {};
            if (guid) {
              migratedSense.guid = guid;
            }
            if (definitionOrGloss?.length) {
              migratedSense.definitionorgloss = definitionOrGloss;
            }
            if (examplesContents?.length) {
              migratedSense.examplescontents = examplesContents;
            }
            if (semanticDomains?.length) {
              migratedSense.semanticdomains = semanticDomains;
            }
            return migratedSense;
          },
        );
      }

      // Step 2: Try to derive missing fields from displayXhtml
      this.parser = cheerio.load(migratedEntry.displayXhtml);

      // these can exist in addition to or in place of mainheadword
      ['headword', 'citationform', 'lexemeform'].forEach((langClass) => {
        if (migratedEntry.displayXhtml.includes(`class="${langClass}"`)) {
          const langObject = this.getLangObjects(langClass);
          if (langObject.length) {
            migratedEntry[langClass] = langObject;
          }
        }
      });

      // some dictionaries use this instead of partofspeech
      ['graminfoabbrev'].forEach((langClass) => {
        if (migratedEntry.displayXhtml.includes(`class="${langClass}"`)) {
          const langObject = this.getLangObjects(langClass);
          if (langObject.length) {
            migratedEntry.morphosyntaxanalysis[langClass] = langObject;
          }
        }
      });

      return db
        .collection(entriesCollection)
        .replaceOne({ _id: migratedEntry._id }, migratedEntry, { upsert: true });
    });

    await Promise.all(resultPromises);
    return resultPromises.length;
  }

  getLangObjects = (langParentClass: string) => {
    const langObjects: object[] = [];
    this.parser(`span.${langParentClass} span[lang]`).each((index, elem) => {
      const lang = this.parser(elem).attr('lang');
      const value = this.parser(elem).text();
      langObjects.push({ lang, value });
    });
    return langObjects;
  };
}
