#!/usr/bin/env node
import * as cdk from '@aws-cdk/core';
import { WebonaryCloudApiStack } from '../lib/webonary-cloud-api-stack';
import { envSpecific } from '../lib/config';

const app = new cdk.App();

// eslint-disable-next-line no-new
new WebonaryCloudApiStack(app, envSpecific('WebonaryCloudApiStack'));
