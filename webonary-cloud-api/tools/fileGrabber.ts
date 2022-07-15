import { existsSync, readFileSync, readdirSync } from 'fs';

const BYTE_ORDER_MARKER = '\uFEFF';

class FileGrabber {
  public async getFile(directory: string, fileName: string): Promise<string> {
    const localDir = `data/${directory}/${fileName}`;
    const fileExists = existsSync(localDir);

    let returned = '';
    if (fileExists) {
      returned = readFileSync(localDir).toString();

      // remove BOM from UTF8 files
      const regex = new RegExp(`^${BYTE_ORDER_MARKER}`);
      returned = returned.replace(regex, '');
    }
    return returned;
  }

  public async getFilenames(directory: string): Promise<string[]> {
    return readdirSync(`data/${directory}`);
  }
}

const grabber = new FileGrabber();

export default grabber;
