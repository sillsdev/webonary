import { APIGatewayEvent } from 'aws-lambda';

import { createDictionary, setupMongo } from './databaseSetup';
import { Dictionary } from '../dictionary.model';
import { upsertEntries } from '../postEntry';

import { handler } from '../getDictionary';

setupMongo();

const testUsername = 'test-username';

describe('get Dictionary', () => {
  test('get dictionary entries derived data', async () => {
    const guids = ['guid1', 'guid2'];
    const senseLangs = ['senseLangA', 'senseLangB'];
    const dictionaryId = await createDictionary('{ "mainLanguage": "mainLangA" }');

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
  });

  test('get dictionary reversal entries derived data', async () => {
    const guids = ['guid1', 'guid2'];
    const reversalFormLangs = ['reversalLangA', 'reversalLangB'];
    const dictionaryData: Partial<Dictionary> = {
      reversalLanguages: reversalFormLangs.map((lang) => {
        return { lang, title: `title-${lang}`, letters: ['a'], cssFiles: [''] };
      }),
    };

    const dictionaryId = await createDictionary(JSON.stringify(dictionaryData));

    const posts = guids.map((guid) => {
      return {
        guid,
        reversalform: reversalFormLangs.map((lang) => {
          return { lang, value: `value-${lang}` };
        }),
      };
    });
    posts.push({ guid: '3', reversalform: [{ lang: reversalFormLangs[0], value: 'value' }] });
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
