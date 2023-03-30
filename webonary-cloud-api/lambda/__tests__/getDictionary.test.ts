import { APIGatewayEvent } from 'aws-lambda';

import { createDictionary, setupMongo } from './databaseSetup';
import { Dictionary } from '../dictionary.model';
import { upsertEntries } from '../postEntry';

import { handler } from '../getDictionary';

setupMongo();

const testUsername = 'test-username';

describe('get Dictionary', () => {
  test('entries derived data', async () => {
    const guids = ['guid1', 'guid2'];
    const senseLangs = ['senseLangA', 'senseLangB'];

    const partsOfSpeech = senseLangs
      .map((lang) => {
        return { lang, abbreviation: `part-${lang}` };
      })
      .concat(
        senseLangs.map((lang) => {
          return { lang, abbreviation: `gram-${lang}` };
        }),
      )
      // duplicates
      .concat(
        senseLangs.map((lang) => {
          return { lang, abbreviation: `gram-${lang}` };
        }),
      )
      // unused in entries
      .concat(
        senseLangs.map((lang) => {
          return { lang, abbreviation: `unused-${lang}` };
        }),
      );

    const dictionaryId = await createDictionary(
      JSON.stringify({
        mainLanguage: { lang: 'mainLangA' },
        partsOfSpeech,
      }),
    );

    const posts = guids.map((guid) => {
      return {
        guid,
        senses: [
          {
            definitionorgloss: senseLangs.map((lang) => {
              return { lang, value: `sense-${lang}` };
            }),
          },
        ],
        morphosyntaxanalysis: {
          partofspeech: senseLangs.map((lang) => {
            return { lang, value: `part-${lang}` };
          }),
          graminfoabbrev: senseLangs.map((lang) => {
            return { lang, value: `gram-${lang}` };
          }),
        },
      };
    });

    await upsertEntries(posts, false, dictionaryId, testUsername);

    const event: Partial<APIGatewayEvent> = {
      pathParameters: { dictionaryId },
    };
    const results = await handler(event as APIGatewayEvent);
    const dictionary = JSON.parse(results.body);

    expect(dictionary.definitionOrGlossLangs).toStrictEqual(['senseLangA', 'senseLangB']);
    expect(dictionary.mainLanguage.entriesCount).toBe(2);

    // should not contain duplicates or those unused in entries
    expect(dictionary.partsOfSpeech).toStrictEqual(
      senseLangs
        .map((lang) => {
          return { lang, abbreviation: `part-${lang}`, entriesCount: 2 };
        })
        .concat(
          senseLangs.map((lang) => {
            return { lang, abbreviation: `gram-${lang}`, entriesCount: 2 };
          }),
        ),
    );
  });

  test('reversal entries derived data', async () => {
    const guids = ['guid1', 'guid2'];
    const reversalformLangs = ['reversalLangA', 'reversalLangB'];
    const dictionaryData: Partial<Dictionary> = {
      mainLanguage: { lang: 'mainLangA', title: '', letters: [''], cssFiles: [''] },
      reversalLanguages: reversalformLangs.map((lang) => {
        return { lang, title: `title-${lang}`, letters: ['a'], cssFiles: [''] };
      }),
    };

    const dictionaryId = await createDictionary(JSON.stringify(dictionaryData));

    const posts = guids.map((guid) => {
      return {
        guid,
        reversalform: reversalformLangs.map((lang) => {
          return { lang, value: `value-${lang}` };
        }),
      };
    });
    posts.push({ guid: '3', reversalform: [{ lang: reversalformLangs[0], value: 'value' }] });
    await upsertEntries(posts, true, dictionaryId, testUsername);

    const event: Partial<APIGatewayEvent> = {
      pathParameters: { dictionaryId },
    };
    const results = await handler(event as APIGatewayEvent);
    const dictionary = JSON.parse(results.body);
    expect(dictionary.reversalLanguages[0].entriesCount).toBe(3);
    expect(dictionary.reversalLanguages[1].entriesCount).toBe(2);
  });
});
