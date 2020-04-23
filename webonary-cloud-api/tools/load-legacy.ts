#!/usr/bin/env node
/* eslint-disable no-console */
import axios, { AxiosBasicCredentials, AxiosResponse, AxiosError, AxiosRequestConfig } from 'axios';
import * as mime from 'mime-types';
import * as fs from 'fs';
import fileGrabber from './fileGrabber';
import { FlexXhtmlParser, Entry, EntryFile } from './flexXhtmlParser';

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

async function loadEntry(
  dictionary: string,
  entry: Entry[],
  credentials: AxiosBasicCredentials,
): Promise<AxiosResponse | undefined> {
  const path = `/load/entry/${dictionary}`;
  const data = JSON.stringify(entry);
  const config: AxiosRequestConfig = { auth: credentials };

  try {
    return await axios.post(path, data, config);
  } catch (error) {
    handleAxiosError(error);
  }

  return undefined;
}

async function loadFile(
  dictionary: string,
  file: string,
  credentials: AxiosBasicCredentials,
): Promise<AxiosResponse | undefined> {
  const path = `/load/file/${dictionary}`;
  const data = JSON.stringify({
    objectId: `${dictionary}/${file}`,
    action: 'putObject',
  });
  const config: AxiosRequestConfig = { auth: credentials };

  try {
    const response = await axios.post(path, data, config);
    const signedUrl = response.data;
    if (typeof signedUrl === 'string') {
      const filePath = `data/${dictionary}/${file}`;
      if (fs.existsSync(filePath)) {
        const fileContent = fs.readFileSync(`data/${dictionary}/${file}`);
        const fileConfig: AxiosRequestConfig = {
          headers: { 'Content-Type': mime.lookup(file) },
        };
        try {
          return await axios.put(signedUrl, fileContent, fileConfig);
        } catch (error) {
          logMessage(`loadEntry Error: ${JSON.stringify(error.message)}`);
        }
      } else {
        logMessage(`Warning: File ${file} does not exist!`);
      }
    }
  } catch (error) {
    logMessage(`loadFile Error: ${JSON.stringify(error.message)}`);
  }

  return undefined;
}

// TODO: Replace with yarg for better processing
const [, , ...args] = process.argv;

if (
  process.env.API_DOMAIN_NAME &&
  process.env.API_DOMAIN_BASEPATH &&
  process.env.API_DOMAIN_CERT_ARN
) {
  axios.defaults.baseURL = `https://${process.env.API_DOMAIN_NAME}/${process.env.API_DOMAIN_BASEPATH}`;
} else {
  axios.defaults.baseURL = process.env.LOAD_BASE_URL ?? 'https://localhost:3000';
}

axios.defaults.headers.post['Content-Type'] = 'application/json';

const username = process.env.WEBONARY_USERNAME ?? '';
const password = process.env.WEBONARY_PASSWORD ?? '';
const credentials: AxiosBasicCredentials = { username, password };

if (args[0] && args[1]) {
  (async (): Promise<void> => {
    logMessage(`Importing ${args} to ${axios.defaults.baseURL} for ${username}`);

    const CHUNK_LOAD_ENTRY_SIZE = 50; // Mongo Altas allows 100 transactions a second, 500 simultaneous
    const CHUNK_LOAD_FILE_SIZE = 100; // AWS Lambda allows 1000 simultaneous connections
    const dictionary = args[0];
    const file = args[1];
    const toBeParsed = await fileGrabber.getFile(dictionary, file);
    const parser = new FlexXhtmlParser(toBeParsed, { dictionary });

    const startProcessingTime = Date.now();
    const startParsingTime = startProcessingTime;
    logMessage('Start parsing...');

    await parser.parse();
    logMessage(`Finished parsing ${parser.parsedItems.length} entries`, startParsingTime);

    const limit = Number(args[2]);
    if (limit) {
      logMessage(`Limiting to ${limit.toString()} entries`);
      parser.parsedItems = parser.parsedItems.slice(0, limit);
    }

    const startLoadingEntriesTime = Date.now();
    logMessage(`Start loading entries in chunks of ${CHUNK_LOAD_ENTRY_SIZE}...`);

    const chunkedParsedItems: Entry[][] = chunkArray(parser.parsedItems, CHUNK_LOAD_ENTRY_SIZE);

    // we need to allow synchronous processing in order to make sure not to overwhelm api gateway
    // eslint-disable-next-line no-restricted-syntax
    for (const [index, chunk] of chunkedParsedItems.entries()) {
      const startChunkTime = Date.now();
      logMessage(`Loading chunk ${index + 1}...`);

      /* 
      NOTE: It turns out (not surprisingly) that a single load with multiple entries
      a lot faster than many single loads running async

      const promises = chunk.map((entry): Promise<void> => {
          return loadEntry(dictionary, [entry], credentials);                    
      });
      await Promise.all(promises);
      */

      // eslint-disable-next-line no-await-in-loop
      await loadEntry(dictionary, chunk, credentials);

      logMessage(`Finished loading chunk of ${CHUNK_LOAD_ENTRY_SIZE}`, startChunkTime);
    }

    logMessage(`Finished loading ${parser.parsedItems.length} entries`, startLoadingEntriesTime);

    const startLoadingFilesTime = Date.now();
    logMessage(' Start loading files...');

    const entryFiles = parser.parsedItems.reduce(
      (files: EntryFile[], entry: Entry): EntryFile[] => {
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
    logMessage(`Start loading files in chunks of ${CHUNK_LOAD_FILE_SIZE}...`);

    const chunkedEntryFiles: EntryFile[][] = chunkArray(entryFiles, CHUNK_LOAD_FILE_SIZE);

    // we need to allow synchronous processing in order to make sure not to overwhelm api gateway
    // eslint-disable-next-line no-restricted-syntax
    for (const [index, chunk] of chunkedEntryFiles.entries()) {
      const startChunkTime = Date.now();
      logMessage(`Loading chunk ${index + 1}...`);

      const promises = chunk.map(
        (entryFile): Promise<AxiosResponse | undefined> => {
          return loadFile(dictionary, entryFile.src, credentials);
        },
      );

      /* eslint-disable no-await-in-loop */
      await Promise.all(promises);

      logMessage(`Finished loading chunk of ${CHUNK_LOAD_FILE_SIZE}`, startChunkTime);
    }

    logMessage(`Finished loading ${entryFiles.length} files`, startLoadingFilesTime);

    logMessage(`Finished processing ${dictionary}`, startProcessingTime);
  })();
} else {
  logMessage('Usage: import-entries DICTIONARY_NAME CONFIGURED_XHTML_FILE_NAME LIMIT_ENTRY');
}
