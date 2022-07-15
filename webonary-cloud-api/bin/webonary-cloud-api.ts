#!/usr/bin/env node
import { App } from '@aws-cdk/core';
import { WebonaryCloudApiStack } from '../lib/webonary-cloud-api-stack';
import { envSpecific } from '../lib/config';

const app = new App();

// eslint-disable-next-line no-new
new WebonaryCloudApiStack(app, envSpecific('WebonaryCloudApiStack'));
