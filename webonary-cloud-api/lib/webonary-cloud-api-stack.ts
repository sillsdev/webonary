import * as cdk from '@aws-cdk/core';
import * as lambda from '@aws-cdk/aws-lambda';
import * as apigateway from '@aws-cdk/aws-apigateway';
import * as iam from '@aws-cdk/aws-iam';
import * as s3 from '@aws-cdk/aws-s3';
import { Certificate } from '@aws-cdk/aws-certificatemanager';

function defaultLambdaFunctionProps(functionName: string): lambda.FunctionProps {
  const props: lambda.FunctionProps = {
    functionName,
    runtime: lambda.Runtime.NODEJS_12_X,
    code: new lambda.AssetCode('lambda'),
    handler: `${functionName}.handler`,
    timeout: cdk.Duration.seconds(60),
  };
  return props;
}

export class WebonaryCloudApiStack extends cdk.Stack {
  constructor(scope: cdk.App, id: string, props?: cdk.StackProps) {
    super(scope, id, props);

    // Webonary web site
    const WEBONARY_URL = process.env.WEBONARY_URL ?? 'https://www.webonary.org';
    const WEBONARY_AUTH_PATH = process.env.WEBONARY_AUTH_PATH ?? '/wp-json/webonary/import';

    // Mongo
    const { DB_URI, DB_USERNAME, DB_PASSWORD } = process.env;
    const DB_URL = `mongodb+srv://${DB_USERNAME}:${DB_PASSWORD}@${DB_URI}`;
    const DB_NAME = process.env.DB_NAME ?? 'webonary';

    // S3
    const dictionaryBucket = new s3.Bucket(this, 'dictionaryBucket', {
      encryption: s3.BucketEncryption.S3_MANAGED,
      publicReadAccess: true,
    });

    // create lambda policy statement for operating over s3
    const lambdaS3PolicyStatement = new iam.PolicyStatement({
      effect: iam.Effect.ALLOW,
      actions: ['s3:PutObject', 's3:GetObject'],
      resources: [`${dictionaryBucket.bucketArn}/*`],
    });

    // Lambda functions

    // API Authorizer for loading dictonary file or entry
    const loadAuthorizeFuction = new lambda.Function(
      this,
      'loadAuthorize',
      Object.assign(defaultLambdaFunctionProps('loadAuthorize'), {
        environment: {
          WEBONARY_URL,
          WEBONARY_AUTH_PATH,
        },
      }),
    );

    const loadAuthorizer = new apigateway.RequestAuthorizer(this, 'loadAuthorizer', {
      handler: loadAuthorizeFuction,
      identitySources: [apigateway.IdentitySource.header('Authorization')],
    });

    const s3AuthorizeFunction = new lambda.Function(this, 's3Authorize', {
      runtime: lambda.Runtime.NODEJS_12_X,
      code: new lambda.AssetCode('lambda'),
      handler: 's3Authorize.handler',
      environment: {
        S3_BUCKET: dictionaryBucket.bucketName,
      },
    });

    s3AuthorizeFunction.addToRolePolicy(lambdaS3PolicyStatement);

    // eslint-disable-next-line no-new
    new lambda.CfnPermission(this, 's3AuthorizeApiGtwLambdaPermission', {
      functionName: s3AuthorizeFunction.functionArn,
      action: 'lambda:InvokeFunction',
      principal: 'apigateway.amazonaws.com',
    });

    const loadEntryFunction = new lambda.Function(
      this,
      'loadEntry',
      Object.assign(defaultLambdaFunctionProps('loadEntry'), { environment: { DB_URL, DB_NAME } }),
    );

    const getEntryFunction = new lambda.Function(
      this,
      'getEntry',
      Object.assign(defaultLambdaFunctionProps('getEntry'), { environment: { DB_URL, DB_NAME } }),
    );

    const browseEntriesFunction = new lambda.Function(
      this,
      'browseEntries',
      Object.assign(defaultLambdaFunctionProps('browseEntries'), {
        environment: { DB_URL, DB_NAME },
      }),
    );

    const searchEntriesFunction = new lambda.Function(
      this,
      'searchEntries',
      Object.assign(defaultLambdaFunctionProps('searchEntries'), {
        environment: { DB_URL, DB_NAME },
      }),
    );

    // API and resources
    const api = new apigateway.RestApi(this, 'webonary-cloud-api', {
      restApiName: 'webonaryCloudApi',
    });

    const domainName = process.env.API_DOMAIN_NAME
    const domainCertArn = process.env.API_DOMAIN_CERT_ARN;
    if (domainName && domainCertArn) {
      const certificate = Certificate.fromCertificateArn(
        this,
        'apiDomainCertificate',
        domainCertArn,
      );

      const apiDomainName = new apigateway.DomainName(this, 'apiDomain', {
        domainName,
        certificate,
      });

      const basePath = process.env.API_DOMAIN_BASEPATH;
      if (basePath) {
        apiDomainName.addBasePathMapping(api, { basePath });
      }
    }

    // Loading of file and data are protected via Webonary basic auth
    const apiLoad = api.root.addResource('load');

    const apiLoadFile = apiLoad.addResource('file');
    const apiLoadFileForDictionary = apiLoadFile.addResource('{dictionary}');
    const s3AuthorizeLambda = new apigateway.LambdaIntegration(s3AuthorizeFunction);
    apiLoadFileForDictionary.addMethod('POST', s3AuthorizeLambda, {
      authorizer: loadAuthorizer,
    });

    const apiLoadEntry = apiLoad.addResource('entry');
    const apiLoadEntryForDictionary = apiLoadEntry.addResource('{dictionary}');
    const loadEntryLambda = new apigateway.LambdaIntegration(loadEntryFunction);
    apiLoadEntryForDictionary.addMethod('POST', loadEntryLambda, {
      authorizer: loadAuthorizer,
    });

    // Get single entry
    const apiGet = api.root.addResource('get');
    const apiGetDictionary = apiGet.addResource('{dictionary}');
    const getEntryLambda = new apigateway.LambdaIntegration(getEntryFunction);
    apiGetDictionary.addMethod('GET', getEntryLambda);

    // Browse entries, all and by starting letter
    const apiBrowse = api.root.addResource('browse');
    const apiBrowseDictionary = apiBrowse.addResource('{dictionary}');
    const browseEntriesLambda = new apigateway.LambdaIntegration(browseEntriesFunction);
    apiBrowseDictionary.addMethod('GET', browseEntriesLambda);

    // Search entries
    const apiSearch = api.root.addResource('search');
    const apiSearchDictionary = apiSearch.addResource('{dictionary}');
    const searchEntriesLambda = new apigateway.LambdaIntegration(searchEntriesFunction);
    apiSearchDictionary.addMethod('GET', searchEntriesLambda);

    // the main magic to easily pass the lambda version to stack in another region
    // this output is required

    // eslint-disable-next-line no-new
    new cdk.CfnOutput(this, 'db-url', {
      value: DB_URL,
    });
  }
}

export default WebonaryCloudApiStack;
