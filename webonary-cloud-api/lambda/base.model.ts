/* eslint-disable max-classes-per-file */
export interface PostResult {
  updatedAt: string;
  updatedCount: number;
  insertedCount: number;
  insertedIds?: string[];
  message?: string;
}
export interface DbFindParameters {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  [key: string]: any;
}
