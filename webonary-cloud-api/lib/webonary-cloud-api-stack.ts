import * as cdk from '@aws-cdk/core';
import * as lambda from '@aws-cdk/aws-lambda';
import * as apigateway from '@aws-cdk/aws-apigateway';
import * as iam from '@aws-cdk/aws-iam';
import * as s3 from '@aws-cdk/aws-s3';
import { Certificate } from '@aws-cdk/aws-certificatemanager';
import { envSpecific } from './config';

function defaultLambdaFunctionProps(functionName: string, environment = {}): lambda.FunctionProps {
  const props: lambda.FunctionProps = {
    runtime: lambda.Runtime.NODEJS_14_X,
    code: new lambda.AssetCode('lambda'),
    handler: `${functionName}.handler`,
    timeout: cdk.Duration.seconds(60),
    environment,
  };
  return props;
}

export class WebonaryCloudApiStack extends cdk.Stack {
  constructor(scope: cdk.App, id: string, props?: cdk.StackProps) {
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

    // S3
    const dictionaryBucket = new s3.Bucket(this, envSpecific('dictionaryBucket'), {
      encryption: s3.BucketEncryption.S3_MANAGED,
      publicReadAccess: true,
      bucketName: S3_DOMAIN_NAME,
    });

    // Lambda functions

    // API Authorizer for posting dictionary file or entry
    const methodAuthorizeFunction = new lambda.Function(
      this,
      'methodAuthorize',
      defaultLambdaFunctionProps('methodAuthorize', { WEBONARY_URL, WEBONARY_AUTH_PATH }),
    );

    const methodAuthorizer = new apigateway.RequestAuthorizer(this, 'methodAuthorizer', {
      handler: methodAuthorizeFunction,
      identitySources: [apigateway.IdentitySource.header('Authorization')],
    });

    const s3AuthorizeFunction = new lambda.Function(
      this,
      's3Authorize',
      defaultLambdaFunctionProps('s3Authorize', { S3_DOMAIN_NAME }),
    );

    // Give permission to list add and get file
    s3AuthorizeFunction.addToRolePolicy(
      new iam.PolicyStatement({
        effect: iam.Effect.ALLOW,
        actions: ['s3:PutObject', 's3:GetObject'],
        resources: [`${dictionaryBucket.bucketArn}/*`],
      }),
    );

    // eslint-disable-next-line no-new
    new lambda.CfnPermission(this, 's3AuthorizeApiGtwLambdaPermission', {
      functionName: s3AuthorizeFunction.functionArn,
      action: 'lambda:InvokeFunction',
      principal: 'apigateway.amazonaws.com',
    });

    const postDictionaryFunction = new lambda.Function(
      this,
      'postDictionary',
      defaultLambdaFunctionProps('postDictionary', {
        MONGO_DB_URI,
        MONGO_DB_NAME,
        WEBONARY_URL,
        WEBONARY_RESET_DICTIONARY_PATH,
      }),
    );

    const getDictionaryFunction = new lambda.Function(
      this,
      'getDictionary',
      defaultLambdaFunctionProps('getDictionary', { MONGO_DB_URI, MONGO_DB_NAME }),
    );

    const deleteDictionaryFunction = new lambda.Function(
      this,
      'deleteDictionary',
      defaultLambdaFunctionProps('deleteDictionary', {
        MONGO_DB_URI,
        MONGO_DB_NAME,
        S3_DOMAIN_NAME,
      }),
    );

    // Give permission to list all files in the dictionary folder, and delete each
    deleteDictionaryFunction.addToRolePolicy(
      new iam.PolicyStatement({
        effect: iam.Effect.ALLOW,
        actions: ['s3:ListBucket'],
        resources: [`${dictionaryBucket.bucketArn}`],
      }),
    );

    deleteDictionaryFunction.addToRolePolicy(
      new iam.PolicyStatement({
        effect: iam.Effect.ALLOW,
        actions: ['s3:DeleteObject'],
        resources: [`${dictionaryBucket.bucketArn}/*`],
      }),
    );

    const postEntryFunction = new lambda.Function(
      this,
      'postEntry',
      defaultLambdaFunctionProps('postEntry', { MONGO_DB_URI, MONGO_DB_NAME }),
    );

    const getEntryFunction = new lambda.Function(
      this,
      'getEntry',
      defaultLambdaFunctionProps('getEntry', { MONGO_DB_URI, MONGO_DB_NAME }),
    );

    const deleteEntryFunction = new lambda.Function(
      this,
      'deleteEntry',
      defaultLambdaFunctionProps('deleteEntry', { MONGO_DB_URI, MONGO_DB_NAME }),
    );

    const browseEntriesFunction = new lambda.Function(
      this,
      'browseEntries',
      defaultLambdaFunctionProps('browseEntries', { MONGO_DB_URI, MONGO_DB_NAME }),
    );

    const searchEntriesFunction = new lambda.Function(
      this,
      'searchEntries',
      defaultLambdaFunctionProps('searchEntries', { MONGO_DB_URI, MONGO_DB_NAME }),
    );

    // API and resources
    const api = new apigateway.RestApi(this, envSpecific('webonary-cloud-api'), {
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

      const apiDomainName = new apigateway.DomainName(this, envSpecific('apiDomain'), {
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
    const s3AuthorizeLambda = new apigateway.LambdaIntegration(s3AuthorizeFunction);
    apiPostFileForDictionary.addMethod('POST', s3AuthorizeLambda, {
      authorizer: methodAuthorizer,
    });

    const apiPostDictionary = apiPost.addResource('dictionary');
    const apiPostDictionaryForDictionary = apiPostDictionary.addResource('{dictionaryId}');
    const postDictionaryLambda = new apigateway.LambdaIntegration(postDictionaryFunction);
    apiPostDictionaryForDictionary.addMethod('POST', postDictionaryLambda, {
      authorizer: methodAuthorizer,
    });

    const apiPostEntry = apiPost.addResource('entry');
    const apiPostEntryForDictionary = apiPostEntry.addResource('{dictionaryId}');
    const postEntryLambda = new apigateway.LambdaIntegration(postEntryFunction);
    apiPostEntryForDictionary.addMethod('POST', postEntryLambda, {
      authorizer: methodAuthorizer,
    });

    // delete document and its children
    const apiDelete = api.root.addResource('delete');

    const apiDeleteDictionary = apiDelete.addResource('dictionary');
    const apiDeleteDictionaryForDictionary = apiDeleteDictionary.addResource('{dictionaryId}');
    const deleteDictionaryLambda = new apigateway.LambdaIntegration(deleteDictionaryFunction);
    apiDeleteDictionaryForDictionary.addMethod('DELETE', deleteDictionaryLambda, {
      authorizer: methodAuthorizer,
    });

    const apiDeleteEntry = apiDelete.addResource('entry');
    const apiDeleteEntryForDictionary = apiDeleteEntry.addResource('{dictionaryId}');
    const deleteEntryLambda = new apigateway.LambdaIntegration(deleteEntryFunction);
    apiDeleteEntryForDictionary.addMethod('DELETE', deleteEntryLambda, {
      authorizer: methodAuthorizer,
    });

    // Get a single document
    const apiGet = api.root.addResource('get');

    // dictionary meta data
    const apiGetDictionary = apiGet.addResource('dictionary');
    const apiGetDictionaryById = apiGetDictionary.addResource('{dictionaryId}');
    const getDictionaryLambda = new apigateway.LambdaIntegration(getDictionaryFunction);
    apiGetDictionaryById.addMethod('GET', getDictionaryLambda);

    // dictionary entry
    const apiGetEntry = apiGet.addResource('entry');
    const apiGetEntryByDictionaryId = apiGetEntry.addResource('{dictionaryId}');
    const getEntryLambda = new apigateway.LambdaIntegration(getEntryFunction);
    apiGetEntryByDictionaryId.addMethod('GET', getEntryLambda);

    // Browse documents
    const apiBrowse = api.root.addResource('browse');

    // Browse entries, all and by starting letter
    const apiBrowseEntries = apiBrowse.addResource('entry');
    const apiBrowseEntriesByDictionaryId = apiBrowseEntries.addResource('{dictionaryId}');
    const browseEntriesLambda = new apigateway.LambdaIntegration(browseEntriesFunction);
    apiBrowseEntriesByDictionaryId.addMethod('GET', browseEntriesLambda);

    // Search documents
    const apiSearch = api.root.addResource('search');

    // Search entries
    const apiSearchEntries = apiSearch.addResource('entry');
    const apiSearchEntriesByDictionaryId = apiSearchEntries.addResource('{dictionaryId}');

    const searchEntriesLambda = new apigateway.LambdaIntegration(searchEntriesFunction);
    apiSearchEntriesByDictionaryId.addMethod('GET', searchEntriesLambda);

    // the main magic to easily pass the lambda version to stack in another region
    // this output is required

    // eslint-disable-next-line no-new
    new cdk.CfnOutput(this, 'db-url', {
      value: MONGO_DB_URI,
    });
  }
}

export default WebonaryCloudApiStack;
