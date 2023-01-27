import { Construct } from 'constructs';
import { App, CfnOutput, Duration, Stack, StackProps } from 'aws-cdk-lib';
import { CfnPermission, Runtime } from 'aws-cdk-lib/aws-lambda';
import { NodejsFunction, NodejsFunctionProps } from 'aws-cdk-lib/aws-lambda-nodejs';
import { Effect, PolicyStatement } from 'aws-cdk-lib/aws-iam';
import { Bucket, BucketEncryption } from 'aws-cdk-lib/aws-s3';
import { Certificate } from 'aws-cdk-lib/aws-certificatemanager';
import {
  DomainName,
  IdentitySource,
  LambdaIntegration,
  RequestAuthorizer,
  RestApi,
} from 'aws-cdk-lib/aws-apigateway';

import { readFileSync } from 'fs';

import { envSpecific } from './config';

const getExternalDependencies = (packageFile: string) => {
  const packageInfo = JSON.parse(readFileSync(packageFile).toString());
  return Object.keys(packageInfo.devDependencies);
};

const createLambdaFunction = (
  scope: Construct,
  handlerName: string,
  optionsOverrides: NodejsFunctionProps = {},
): NodejsFunction => {
  const functionName = `${scope.toString()}-${handlerName}`;
  const externalDependencies = getExternalDependencies('./lambda/package.json');

  const functionProps: NodejsFunctionProps = {
    runtime: Runtime.NODEJS_14_X,
    entry: `./lambda/${handlerName}.ts`,
    functionName,
    timeout: Duration.seconds(60), // maximum value is 15 minutes
    ...optionsOverrides,
    environment: {
      ...optionsOverrides.environment,
    },
    bundling: {
      ...optionsOverrides.bundling,
      minify: true,
      sourceMap: true,
      externalModules: [...externalDependencies],
    },
  };

  return new NodejsFunction(scope, functionName, functionProps);
};

export class WebonaryCloudApiStack extends Stack {
  constructor(scope: App, id: string, props?: StackProps) {
    super(scope, id, props);

    // Webonary web site
    const WEBONARY_URL = process.env.WEBONARY_URL ?? 'https://www.webonary.org';
    const WEBONARY_AUTH_PATH =
      process.env.WEBONARY_AUTH_PATH ?? '/wp-json/webonary-cloud/v1/validate';
    const WEBONARY_RESET_DICTIONARY_PATH =
      process.env.WEBONARY_RESET_DICTIONARY_PATH ?? '/wp-json/webonary-cloud/v1/resetDictionary';
    const S3_DOMAIN_NAME = process.env.S3_DOMAIN_NAME ?? 'cloud-storage.webonary.org';

    // Mongo
    const MONGO_DB_NAME = process.env.MONGO_DB_NAME ?? 'webonary';
    const MONGO_DB_URI = `${process.env.MONGO_DB_URI}/${MONGO_DB_NAME}?retryWrites=true&w=majority`;

    // Maintenance mode, to be set manually in Lambda config as needed
    const MAINTENANCE_MODE = ''; // put a truthy value to activate maintenance mode, e.g. 1
    const MAINTENANCE_MODE_MESSAGE = ''; // message to return with 503

    // S3
    const dictionaryBucket = new Bucket(this, envSpecific('dictionaryBucket'), {
      encryption: BucketEncryption.S3_MANAGED,
      publicReadAccess: true,
      bucketName: S3_DOMAIN_NAME,
    });

    // Lambda functions

    // API Authorizer for posting dictionary file or entry
    const methodAuthorizeFunction = createLambdaFunction(this, 'methodAuthorize', {
      environment: { WEBONARY_URL, WEBONARY_AUTH_PATH },
    });

    const methodAuthorizer = new RequestAuthorizer(this, 'methodAuthorizer', {
      handler: methodAuthorizeFunction,
      identitySources: [IdentitySource.header('Authorization')],
    });

    const s3AuthorizeFunction = createLambdaFunction(this, 's3Authorize', {
      environment: { S3_DOMAIN_NAME },
    });

    // Give permission to list add and get file
    s3AuthorizeFunction.addToRolePolicy(
      new PolicyStatement({
        effect: Effect.ALLOW,
        actions: ['s3:PutObject', 's3:GetObject'],
        resources: [`${dictionaryBucket.bucketArn}/*`],
      }),
    );

    // eslint-disable-next-line no-new
    new CfnPermission(this, 's3AuthorizeApiGtwLambdaPermission', {
      functionName: s3AuthorizeFunction.functionArn,
      action: 'lambda:InvokeFunction',
      principal: 'apigateway.amazonaws.com',
    });

    const postDictionaryFunction = createLambdaFunction(this, 'postDictionary', {
      environment: {
        MAINTENANCE_MODE,
        MAINTENANCE_MODE_MESSAGE,
        MONGO_DB_URI,
        MONGO_DB_NAME,
        WEBONARY_URL,
        WEBONARY_RESET_DICTIONARY_PATH,
      },
    });

    const getDictionaryFunction = createLambdaFunction(this, 'getDictionary', {
      environment: { MONGO_DB_URI, MONGO_DB_NAME },
    });

    const deleteDictionaryFunction = createLambdaFunction(this, 'deleteDictionary', {
      environment: {
        MAINTENANCE_MODE,
        MAINTENANCE_MODE_MESSAGE,
        MONGO_DB_URI,
        MONGO_DB_NAME,
        S3_DOMAIN_NAME,
      },
    });

    // Give permission to list all files in the dictionary folder, and delete each
    deleteDictionaryFunction.addToRolePolicy(
      new PolicyStatement({
        effect: Effect.ALLOW,
        actions: ['s3:ListBucket'],
        resources: [`${dictionaryBucket.bucketArn}`],
      }),
    );

    deleteDictionaryFunction.addToRolePolicy(
      new PolicyStatement({
        effect: Effect.ALLOW,
        actions: ['s3:DeleteObject'],
        resources: [`${dictionaryBucket.bucketArn}/*`],
      }),
    );

    const postEntryFunction = createLambdaFunction(this, 'postEntry', {
      environment: { MAINTENANCE_MODE, MAINTENANCE_MODE_MESSAGE, MONGO_DB_URI, MONGO_DB_NAME },
    });

    const getEntryFunction = createLambdaFunction(this, 'getEntry', {
      environment: { MONGO_DB_URI, MONGO_DB_NAME },
    });

    const deleteEntryFunction = createLambdaFunction(this, 'deleteEntry', {
      environment: { MAINTENANCE_MODE, MAINTENANCE_MODE_MESSAGE, MONGO_DB_URI, MONGO_DB_NAME },
    });

    const browseEntriesFunction = createLambdaFunction(this, 'browseEntries', {
      environment: { MONGO_DB_URI, MONGO_DB_NAME },
    });

    const searchEntriesFunction = createLambdaFunction(this, 'searchEntries', {
      environment: { MONGO_DB_URI, MONGO_DB_NAME },
    });

    // API and resources
    const api = new RestApi(this, envSpecific('webonary-cloud-api'), {
      restApiName: envSpecific('webonaryCloudApi'),
    });

    const domainName = process.env.API_DOMAIN_NAME;
    const domainCertArn = process.env.API_DOMAIN_CERT_ARN;
    if (domainName && domainCertArn) {
      const certificate = Certificate.fromCertificateArn(
        this,
        envSpecific('apiDomainCertificate'),
        domainCertArn,
      );

      const apiDomainName = new DomainName(this, envSpecific('apiDomain'), {
        domainName,
        certificate,
      });

      const basePath = process.env.API_DOMAIN_BASE_PATH;
      if (basePath) {
        apiDomainName.addBasePathMapping(api, { basePath });
      }
    }

    // Posting of file and data are protected via Webonary basic auth
    const apiPost = api.root.addResource('post');

    const apiPostFile = apiPost.addResource('file');
    const apiPostFileForDictionary = apiPostFile.addResource('{dictionaryId}');
    const s3AuthorizeLambda = new LambdaIntegration(s3AuthorizeFunction);
    apiPostFileForDictionary.addMethod('POST', s3AuthorizeLambda, {
      authorizer: methodAuthorizer,
    });

    const apiPostDictionary = apiPost.addResource('dictionary');
    const apiPostDictionaryForDictionary = apiPostDictionary.addResource('{dictionaryId}');
    const postDictionaryLambda = new LambdaIntegration(postDictionaryFunction);
    apiPostDictionaryForDictionary.addMethod('POST', postDictionaryLambda, {
      authorizer: methodAuthorizer,
    });

    const apiPostEntry = apiPost.addResource('entry');
    const apiPostEntryForDictionary = apiPostEntry.addResource('{dictionaryId}');
    const postEntryLambda = new LambdaIntegration(postEntryFunction);
    apiPostEntryForDictionary.addMethod('POST', postEntryLambda, {
      authorizer: methodAuthorizer,
    });

    // delete document and its children
    const apiDelete = api.root.addResource('delete');

    const apiDeleteDictionary = apiDelete.addResource('dictionary');
    const apiDeleteDictionaryForDictionary = apiDeleteDictionary.addResource('{dictionaryId}');
    const deleteDictionaryLambda = new LambdaIntegration(deleteDictionaryFunction);
    apiDeleteDictionaryForDictionary.addMethod('DELETE', deleteDictionaryLambda, {
      authorizer: methodAuthorizer,
    });

    const apiDeleteEntry = apiDelete.addResource('entry');
    const apiDeleteEntryForDictionary = apiDeleteEntry.addResource('{dictionaryId}');
    const deleteEntryLambda = new LambdaIntegration(deleteEntryFunction);
    apiDeleteEntryForDictionary.addMethod('DELETE', deleteEntryLambda, {
      authorizer: methodAuthorizer,
    });

    // Get a single document
    const apiGet = api.root.addResource('get');

    // dictionary meta data
    const apiGetDictionary = apiGet.addResource('dictionary');
    const apiGetDictionaryById = apiGetDictionary.addResource('{dictionaryId}');
    const getDictionaryLambda = new LambdaIntegration(getDictionaryFunction);
    apiGetDictionaryById.addMethod('GET', getDictionaryLambda);

    // dictionary entry
    const apiGetEntry = apiGet.addResource('entry');
    const apiGetEntryByDictionaryId = apiGetEntry.addResource('{dictionaryId}');
    const getEntryLambda = new LambdaIntegration(getEntryFunction);
    apiGetEntryByDictionaryId.addMethod('GET', getEntryLambda);

    // Browse documents
    const apiBrowse = api.root.addResource('browse');

    // Browse entries, all and by starting letter
    const apiBrowseEntries = apiBrowse.addResource('entry');
    const apiBrowseEntriesByDictionaryId = apiBrowseEntries.addResource('{dictionaryId}');
    const browseEntriesLambda = new LambdaIntegration(browseEntriesFunction);
    apiBrowseEntriesByDictionaryId.addMethod('GET', browseEntriesLambda);

    // Search documents
    const apiSearch = api.root.addResource('search');

    // Search entries
    const apiSearchEntries = apiSearch.addResource('entry');
    const apiSearchEntriesByDictionaryId = apiSearchEntries.addResource('{dictionaryId}');

    const searchEntriesLambda = new LambdaIntegration(searchEntriesFunction);
    apiSearchEntriesByDictionaryId.addMethod('GET', searchEntriesLambda);

    // the main magic to easily pass the lambda version to stack in another region
    // this output is required

    // eslint-disable-next-line no-new
    new CfnOutput(this, 'db-url', {
      value: MONGO_DB_URI,
    });
  }
}

export default WebonaryCloudApiStack;
