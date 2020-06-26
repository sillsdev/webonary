#!/usr/bin/env node
/* eslint-disable no-console */
import axios, { AxiosBasicCredentials, AxiosResponse, AxiosError, AxiosRequestConfig } from 'axios';
import * as mime from 'mime-types';
import * as fs from 'fs';
import { Dictionary, LanguageItem } from '../lambda/dictionary.model';
import {
  DictionaryEntry,
  EntryFile,
  ReversalEntry,
  ENTRY_TYPE_MAIN,
  ENTRY_TYPE_REVERSAL,
} from '../lambda/entry.model';
import fileGrabber from './fileGrabber';
import { FlexXhtmlParserMain } from './flexXhtmlParserMain';
import { FlexXhtmlParserReversal } from './flexXhtmlParserReversal';

function logMessage(message: string, previousTime?: number): void {
  const currentTime = Date.now();
  new Date(currentTime).toString();
  const inSeconds = previousTime
    ? `in ${Math.floor((currentTime - previousTime) / 1000)} seconds`
    : '';
  console.log(`${new Date(currentTime).toString().substr(0, 24)} ${message} ${inSeconds}`);
}

function handleAxiosError(error: AxiosError): void {
  if (error.response) {
    logMessage(JSON.stringify(error.response.data));
    logMessage(error.response.status.toString());
    logMessage(JSON.stringify(error.response.headers));
  } else {
    logMessage(error.message);
  }
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
function chunkArray(array: Array<any>, size: number): Array<any> {
  const chunked = [];
  let index = 0;
  while (index < array.length) {
    chunked.push(array.slice(index, size + index));
    index += size;
  }
  return chunked;
}

async function postDictionary(
  dictionaryId: string,
  dictionary: Dictionary,
  credentials: AxiosBasicCredentials,
): Promise<AxiosResponse | undefined> {
  const path = `/post/dictionary/${dictionaryId}`;
  const data = JSON.stringify(dictionary);
  const config: AxiosRequestConfig = { auth: credentials };

  try {
    return await axios.post(path, data, config);
  } catch (error) {
    handleAxiosError(error);
  }

  return undefined;
}

async function postEntry(
  dictionaryId: string,
  entryType: string,
  entry: DictionaryEntry[],
  credentials: AxiosBasicCredentials,
): Promise<AxiosResponse | undefined> {
  const path = `/post/entry/${dictionaryId}`;
  const data = JSON.stringify(entry);
  const config: AxiosRequestConfig = { params: { entryType }, auth: credentials };

  try {
    return await axios.post(path, data, config);
  } catch (error) {
    handleAxiosError(error);
  }

  return undefined;
}

async function postEntries(
  dictionaryId: string,
  entries: DictionaryEntry[] | ReversalEntry[],
  chunkSize: number,
  credentials: AxiosBasicCredentials,
  reversalLang?: string,
): Promise<void> {
  const startPostingEntriesTime = Date.now();
  logMessage(
    `Start posting ${entries.length} ${reversalLang ??
      'main'} entries in chunks of ${chunkSize}...`,
  );

  const chunkedParsedItem = chunkArray(entries, chunkSize);
  const entryType = reversalLang ? ENTRY_TYPE_REVERSAL : ENTRY_TYPE_MAIN;
  // we need to allow synchronous processing in order to make sure not to overwhelm api gateway
  // eslint-disable-next-line no-restricted-syntax
  for (const [index, chunk] of chunkedParsedItem.entries()) {
    logMessage(`Posting chunk ${index + 1}...`);
    // eslint-disable-next-line no-await-in-loop
    await postEntry(dictionaryId, entryType, chunk, credentials);
  }

  logMessage(
    `Finished posting ${entries.length} ${reversalLang ?? 'main'} entries`,
    startPostingEntriesTime,
  );
}

async function postFile(
  dictionaryId: string,
  file: string,
  credentials: AxiosBasicCredentials,
): Promise<AxiosResponse | undefined> {
  const path = `/post/file/${dictionaryId}`;
  const data = JSON.stringify({
    objectId: `${dictionaryId}/${file}`,
    action: 'putObject',
  });
  const config: AxiosRequestConfig = { auth: credentials };

  try {
    const response = await axios.post(path, data, config);
    const signedUrl = response.data;
    if (typeof signedUrl === 'string') {
      const filePath = `data/${dictionaryId}/${file}`;
      if (fs.existsSync(filePath)) {
        const fileContent = fs.readFileSync(`data/${dictionaryId}/${file}`);
        const fileConfig: AxiosRequestConfig = {
          headers: { 'Content-Type': mime.lookup(file) },
        };
        try {
          return await axios.put(signedUrl, fileContent, fileConfig);
        } catch (error) {
          logMessage(`postEntry Error: ${JSON.stringify(error.message)}`);
        }
      } else {
        logMessage(`Warning: File ${file} does not exist!`);
      }
    }
  } catch (error) {
    logMessage(`postFile Error: ${JSON.stringify(error.message)}`);
  }

  return undefined;
}

// TODO: Replace with yarg for better processing
const [, , ...args] = process.argv;

if (
  process.env.API_DOMAIN_NAME &&
  process.env.API_DOMAIN_BASE_PATH &&
  process.env.API_DOMAIN_CERT_ARN
) {
  axios.defaults.baseURL = `https://${process.env.API_DOMAIN_NAME}/${process.env.API_DOMAIN_BASE_PATH}`;
} else {
  axios.defaults.baseURL = process.env.LOAD_BASE_URL ?? 'https://localhost:8000';
}

axios.defaults.headers.post['Content-Type'] = 'application/json';

const username = process.env.WEBONARY_USERNAME ?? '';
const password = process.env.WEBONARY_PASSWORD ?? '';
const credentials: AxiosBasicCredentials = { username, password };

if (args[0]) {
  (async (): Promise<void> => {
    logMessage(`Importing ${args} to ${axios.defaults.baseURL} for ${username}`);

    const CHUNK_LOAD_ENTRY_SIZE = 50; // Mongo Atlas allows 100 transactions a second, 500 simultaneous
    const CHUNK_LOAD_FILE_SIZE = 100; // AWS Lambda allows 1000 simultaneous connections
    const dictionaryId = args[0];

    const dictionaryFiles = await fileGrabber.getFilenames(dictionaryId);
    if (!dictionaryFiles.length) {
      logMessage(`No data found for ${dictionaryId}!`);
      return;
    }

    const mainCssFiles = [];
    const mainFile = 'configured.xhtml';
    const mainCssFile = 'configured.css';
    if (!dictionaryFiles.includes(mainFile)) {
      logMessage(`${mainFile} or ${mainCssFile} not found!`);
      return;
    }

    mainCssFiles.push(mainCssFile);

    const mainCssOverrideFile = 'ProjectDictionaryOverrides.css';
    if (dictionaryFiles.includes(mainCssOverrideFile)) {
      mainCssFiles.push(mainCssOverrideFile);
    }

    const startProcessingTime = Date.now();
    const startParsingTime = startProcessingTime;
    logMessage('Start parsing main xhtml...');

    const toBeParsed = await fileGrabber.getFile(dictionaryId, mainFile);
    const mainParser = new FlexXhtmlParserMain(toBeParsed, { dictionaryId });

    logMessage(
      `Finished parsing ${mainParser.parsedDictionaryEntries.length} entries`,
      startParsingTime,
    );

    const limit = Number(args[1]);
    if (limit) {
      logMessage(`Limiting to ${limit.toString()} entries`);
      mainParser.parsedDictionaryEntries = mainParser.parsedDictionaryEntries.slice(0, limit);
    }

    const startReversalParsingTime = Date.now();
    logMessage('Start parsing reversal xhtml files...');
    const reversals = await Promise.all(
      dictionaryFiles
        .filter(file => file.endsWith('.xhtml') && file !== mainFile)
        .map(async file => {
          const lang = file.substring(file.lastIndexOf('_') + 1, file.lastIndexOf('.'));
          const parser = new FlexXhtmlParserReversal(
            await fileGrabber.getFile(dictionaryId, file),
            { dictionaryId },
          );

          if (limit) {
            parser.parsedReversalEntries = parser.parsedReversalEntries.slice(0, limit);
          }

          return { lang, parser };
        }),
    );

    logMessage(`Finished parsing ${reversals.length} reversal files`, startReversalParsingTime);

    // post main entries
    await postEntries(
      dictionaryId,
      mainParser.parsedDictionaryEntries,
      CHUNK_LOAD_ENTRY_SIZE,
      credentials,
    );

    // post reversal entries
    // eslint-disable-next-line no-restricted-syntax
    for (const reversal of reversals) {
      // eslint-disable-next-line no-await-in-loop
      await postEntries(
        dictionaryId,
        reversal.parser.parsedReversalEntries,
        CHUNK_LOAD_ENTRY_SIZE,
        credentials,
        reversal.lang,
      );
    }

    const startPostingFilesTime = Date.now();
    logMessage('Start posting files...');

    const entryFiles = mainParser.parsedDictionaryEntries.reduce(
      (files: EntryFile[], entry: DictionaryEntry): EntryFile[] => {
        if (entry.audio.src) {
          files.push(entry.audio);
        }
        if (entry.pictures.length) {
          entry.pictures.forEach(picture => {
            if (picture.src) {
              files.push(picture);
            }
          });
        }
        return files;
      },
      [],
    );

    logMessage(`Found ${entryFiles.length} files to process`);
    logMessage(`Start posting files in chunks of ${CHUNK_LOAD_FILE_SIZE}...`);

    const chunkedEntryFiles: EntryFile[][] = chunkArray(entryFiles, CHUNK_LOAD_FILE_SIZE);

    // we need to allow synchronous processing in order to make sure not to overwhelm api gateway
    // eslint-disable-next-line no-restricted-syntax
    for (const [index, chunk] of chunkedEntryFiles.entries()) {
      const startChunkTime = Date.now();
      logMessage(`Posting chunk ${index + 1}...`);

      const promises = chunk.map(
        (entryFile): Promise<AxiosResponse | undefined> => {
          return postFile(dictionaryId, entryFile.src, credentials);
        },
      );

      /* eslint-disable no-await-in-loop */
      await Promise.all(promises);

      logMessage(`Finished posting chunk of ${CHUNK_LOAD_FILE_SIZE}`, startChunkTime);
    }

    logMessage(`Finished posting ${entryFiles.length} files`, startPostingFilesTime);

    logMessage(`Getting dictionary metadata...`);
    const dictionaryPost = mainParser.getDictionaryData();
    if (dictionaryPost) {
      dictionaryPost.updatedBy = credentials.username;
      dictionaryPost.mainLanguage.cssFiles = mainCssFiles;

      dictionaryPost.reversalLanguages = reversals.map(reversal => {
        const item = new LanguageItem();
        item.lang = reversal.lang;
        item.letters = reversal.parser.parsedLetters;
        item.title = mainParser.parsedLanguages.get(item.lang) ?? '';

        [`reversal_${item.lang}.css`, 'ProjectReversalOverrides.css'].forEach(file => {
          if (dictionaryFiles.includes(file)) {
            item.cssFiles.push(file);
          }
        });

        return item;
      });

      logMessage(`Posting dictionary css files...`);
      const promises = dictionaryFiles
        .filter(file => file.endsWith('.css'))
        .map(
          (file): Promise<AxiosResponse | undefined> => {
            return postFile(dictionaryId, file, credentials);
          },
        );
      await Promise.all(promises);

      logMessage(`Posting dictionary metadata...`);
      const response = await postDictionary(dictionaryId, dictionaryPost, credentials);
      logMessage(JSON.stringify(response?.data));
    }

    logMessage(`Finished processing ${dictionaryId}`, startProcessingTime);
  })();
} else {
  logMessage('Usage: import-entries DICTIONARY_NAME LIMIT_ENTRY');
}
