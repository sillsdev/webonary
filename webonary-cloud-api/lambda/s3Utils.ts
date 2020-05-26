import * as S3 from 'aws-sdk/clients/s3';

const S3_CONFIG: S3.ClientConfiguration = { signatureVersion: 'v4' };

export function getSignedUrl(bucket: string, action: string, objectId: string): string {
  const s3 = new S3(S3_CONFIG);

  return s3.getSignedUrl(action, {
    Bucket: bucket,
    Key: objectId,
    Expires: 30, // URL is good for 30 seconds
  });
}

export async function deleteS3Folder(bucket: string, dictionaryId: string): Promise<number> {
  const listParams = {
    Bucket: bucket,
    Prefix: `${dictionaryId}/`,
  };

  const s3 = new S3(S3_CONFIG);

  const listedObjects = await s3.listObjectsV2(listParams).promise();
  let deletedFilesCount = listedObjects.Contents?.length ?? 0;

  if (listedObjects.Contents && deletedFilesCount) {
    const objectsToDelete = listedObjects.Contents.map(object => {
      const Key = object.Key ?? '';
      return { Key };
    });
    const deleteParams = {
      Bucket: bucket,
      Delete: { Objects: objectsToDelete },
    };

    await s3.deleteObjects(deleteParams).promise();

    // only 1000 can be listed, and thus deleted, at a time, so check if remaining
    if (listedObjects.IsTruncated) {
      deletedFilesCount += await deleteS3Folder(bucket, dictionaryId);
    }
  }

  return deletedFilesCount;
}
