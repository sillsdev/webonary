/* eslint-disable @typescript-eslint/no-explicit-any */
/* eslint-disable import/no-relative-packages */
/* eslint-disable no-await-in-loop */
/* eslint-disable no-console */
import * as cheerio from 'cheerio';
import { Db, UpdateResult } from 'mongodb';
import { MigrationInterface } from 'mongo-migrate-ts';

// use relative path so mongo-migrate cli can find it
import { dbCollectionEntries, DB_COLLECTION_DICTIONARIES } from '../../lambda/db';
import { DbPaths } from '../../lambda/entry.model';

// Legacy data did not always detect definition or gloss fields in senses.
// This data fix will try to populate senses array from displayXhtml when necessary.
export class Migration20230128T2139ZFillDefinitionOrGloss implements MigrationInterface {
  parser: cheerio.Root;

  public async up(db: Db): Promise<any> {
    const dictionaryIds = await this.getDictionaryIds(db);

    // sequentially process to not overburden Mongo
    // eslint-disable-next-line no-restricted-syntax
    for (const dictionaryId of dictionaryIds) {
      console.log(`Checking dictionary ${dictionaryId}... `);

      const resultCount = await this.fillDefinitionOrGloss(db, dictionaryId);
      console.log(`Completed fixing ${resultCount} dictionary ${dictionaryId} entries.`);
    }
  }

  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  public async down(db: Db): Promise<any> {
    console.log('Nothing to undo here!');
  }

  async getDictionaryIds(db: Db): Promise<string[]> {
    const dictionaries = await db.collection(DB_COLLECTION_DICTIONARIES).find({}).toArray();
    return dictionaries.map((dictionary) => dictionary._id.toString()).sort();
  }

  async fillDefinitionOrGloss(db: Db, dictionaryId: string) {
    const entriesCollection = db.collection(dbCollectionEntries(dictionaryId));

    const entriesWithoutDefinitionOrGloss = await entriesCollection
      .find({
        $and: [
          {
            $or: [
              { [DbPaths.ENTRY_DEFINITION_OR_GLOSS_LANG]: { $exists: false } },
              { [DbPaths.ENTRY_DEFINITION_OR_GLOSS_LANG]: '' },
            ],
          },
          {
            $or: [
              { [DbPaths.ENTRY_DEFINITION_LANG]: { $exists: false } },
              { [DbPaths.ENTRY_DEFINITION_LANG]: '' },
            ],
          },
          {
            $or: [
              { [DbPaths.ENTRY_GLOSS_LANG]: { $exists: false } },
              { [DbPaths.ENTRY_GLOSS_LANG]: '' },
            ],
          },
        ],
      })
      .toArray();

    const resultPromises: Promise<UpdateResult>[] = [];
    entriesWithoutDefinitionOrGloss.forEach(async (entry) => {
      // Try to derive missing fields from displayXhtml
      this.parser = cheerio.load(entry.displayXhtml);

      const newSense: Record<string, object> = {};
      ['definitionorgloss', 'definition', 'gloss'].forEach((langClass) => {
        if (entry.displayXhtml.includes(`class="${langClass}"`)) {
          const langObject = this.getLangObjects(langClass);
          if (langObject.length) {
            newSense[langClass] = langObject;
          }
        }
      });

      if (Object.keys(newSense).length) {
        const senses = Array.isArray(entry.senses) ? [newSense].concat(entry.senses) : [newSense];

        const result = entriesCollection.updateOne(
          { _id: entry._id },
          { $set: { senses } },
          { upsert: true },
        );
        resultPromises.push(result);
      }
    });

    await Promise.all(resultPromises);
    return resultPromises.length;
  }

  getLangObjects = (langParentClass: string) => {
    const langObjects: object[] = [];
    this.parser(`span.${langParentClass} span[lang]`).each((index, elem) => {
      const lang = this.parser(elem).attr('lang');
      const value = this.parser(elem).text();
      if (lang && value) {
        langObjects.push({ lang, value });
      }
    });
    return langObjects;
  };
}
