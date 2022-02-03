# Webonary Cloud API (WCA)
Webonary Cloud API provides a way for dictionary data from [FieldWorks (FLex)](https://github.com/sillsdev/FieldWorks) to be stored in AWS Cloud and accessed by applications, including [Webonary (2.0)](https://www.webonary.org).

#TODO:  this instruction is misleading at this point...it should either be moved or the produced docs should be hosted as a github site. Complete list of API calls are found in [Webonary Cloud API Guide](./lambda_api_doc/index.html).

## Architecture
WCA is a serverless, cloud-based architecture consisting of:
1. AWS API Gateway
2. AWS S3
3. AWS Lambda (Typescript)
4. MongoDB Atlas
5. (Optional) AWS Certificate Manager and Route 53

## Technology Stack
1. AWS Cloud Development Kit (CDK) for infrastructure provisioning
2. Typescript for Lamda and CDK
3. AWS Serverless Application Model (SAM) for local Lambda testing
4. Jest for unit testing
5. (Recommended) Visual Studio Code

## Installation and Prerequisites
1. Clone this [repository](https://github.com/sillsdev/webonary.git) using [git](https://git-scm.com/).
   1. Note that this is a monorepo, containing code for Webonary Wordpress site, as well as WCA. If you are interested in only WCA, you might consider using [git sparse-checkout](https://github.blog/2020-01-17-bring-your-monorepo-down-to-size-with-sparse-checkout/).
   2. The code for WCA exists in the directory [webonary-cloud-api](https://github.com/sillsdev/webonary/tree/master/webonary-cloud-api).
#TODO: this doesn't seem useful, I'd suggest removing it.   3. WCA was scaffolded using [cdk init --typescript](https://docs.aws.amazon.com/cdk/latest/guide/getting_started.html).
2. Directory structure:
   1. Root directory is `webonary-cloud-api`.
   2. CDK stack provisioning code is in `lib`.
   3. Lamda functions are in `lambda`.
   4. Tools and utilities are in `tools`.
   5. Unit tests are stored in `__tests__` within the directory where the code to be tested is.
   6. Unit code test coverage information goes to `coverage`.
   7. Once AWS assets are deployed using `cdk deploy`, asset files and CloudFormation templates are stored in `cdk.out`.
3. Install required node modules using [node and npm or a node version manager](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm). Currently, node version 12.4.0 is being used as it is the [latest version](https://aws.amazon.com/blogs/compute/node-js-12-x-runtime-now-available-in-aws-lambda/) supported for Lambda.
   1. In the root directory, install required npm packages using `npm install`.  #TODO: package.lock had to be upgraded, maybe it's time to go ahead and commit that with vuln's fixed as well?
   2. In `lambda` subdirectory, where Lambda functions are stored, install required packages using `npm install`.  #TODO: package.lock had to be upgraded, maybe it's time to go ahead and commit that with vuln's fixed as well?
4. Install [AWS command line interface](https://docs.aws.amazon.com/cli/latest/userguide/install-cliv2.html). #TODO: There you'll find the prerequisites as well as a "Quick setup".  Use `us-east-2` as your default region.  Test your config: `aws sts get-caller-identity` << instead of the following >> and [configure](https://docs.aws.amazon.com/cli/latest/userguide/cli-chap-configure.html) with your AWS credentials.
5. Install [AWS CDK command line interface](https://docs.aws.amazon.com/cdk/latest/guide/getting_started.html) by doing `npm install -g aws-cdk`. #TODO: the following instructions seem premature...when do we deploy?  Maybe these belong a little lower in the README nearer to deployment instructions.  Additionally, can we avoid a global install here? >>>You will need to [set CDK env variables](https://docs.aws.amazon.com/cdk/latest/guide/environments.html) before your deployment, then do `cdk bootstrap` first time you deploy to a new environment.
6. Install [env-cmd npm package](https://www.npmjs.com/package/env-cmd) by doing `npm install -g env-cmd`. Then in your root directory, copy .env.sample to .env and modify it for your testing environment. #TODO: can we avoid a global install here?  And the second part of this is realted to configuration, it might be cleaner to have all the installs done by a single command and then provide a separate configuration section.
7. Install [apidoc](https://apidocjs.com) by doing `npm install -g apidoc`. This will be used to generate API documentation. #TODO: can we avoid a global install here?  And mayeb this make sense to install if/when needed?

## Development
1. This project is already set up for Typescript linting using [eslint](https://eslint.org/) with [AirBnB styles](https://www.npmjs.com/package/eslint-config-airbnb-typescript) and formatting with [prettier](https://github.com/prettier/prettier-eslint).
2. [Visual Studio Code (vscode)](https://code.visualstudio.com/) can be (configured)[https://levelup.gitconnected.com/setting-up-eslint-with-prettier-typescript-and-visual-studio-code-d113bbec9857] so automatic changes by eslint and prettier can be done as you save your code.
   1. Alternately, you can run `npm run lint` from the root directory to detect problems and make automatic changes in all your Typescript files. #TODO:  when I ran this to test the config, it did find (and make) some changes... I was surprised at the types of changes because they seem like they should've never made it into the repo in the first place.  Is linting something that dev's need to remember to do?  If so, we should get it automated in some way or educate dev's to do it before commiting.
3. While coding, you can open a shell terminal or do so within vscode and run `npm run watch` from the root directory for automatic compilation of your Typescript code into javascript.
   1. Alternately, you can run `npm run build` from the root directory. #TODO: it should be noted that build targets are produced in the `bin` and `lamba` directories.
4. To run unit tests using [jest](https://jestjs.io/), from the root directory run `npm run test`.  #TODO: tests fail for me... are there required config's for the tests to run?
   1. To see coverage info, do `npm run test-coverage`. #TODO: did not attmpt since tests are failing.
   2. To capture or re-capture snapshot of CloudFormation template generated by cdk, do `npm run test -- -u` or `npm run test-coverage -- -u`.
5. If you make changes to you cdk stack code, or to any of your Lambda functions, you can deploy your changes by doing `npm run deploy`.
   1. To completely destroy your stack and your code in AWS, do `cdk destroy`.

## Documentation
Documentation for APIs is generated from specially formatted comments using [apidoc](https://apidocjs.com).

1. Add documentation to Typescript files in the `lambda` directory. #TODO: this confused me at first, I thought this was an instruction but it's really about "If documentation is found within `.ts` files in the `lambda` directory, running the following commands will produce documentation in the `lambda_api_doc` directory.
2. Run `npm run apidoc` to generate documentation files in `lambda_api_doc` directory.  #TODO: this generated things in the `lambda_api_doc` directory that what picked up as git changes...should this directory be ignored or are these typically checked in?
3. Running `npm run build` or `npm run deploy` will also cause `apidoc` to be run. #TODO: not true (anymore?)

## Local development and testing
1. During development, you can execute Lambda functions for testing, without deployment to AWS Cloud, using [AWS SAM](https://aws.amazon.com/serverless/sam/).

To do so, run  `npm run deploy-local` from a terminal on your machine or from vscode's own terminal. This executes `cdk synth --no-staging >| template.yaml && sam local start-api`, which will update your CloudFormation template, and start SAM. Once started, your Lambda functions can be executed locally at `http://127.0.0.1:3000` by using tools like [curl](https://curl.haxx.se/) or [postman](https://www.postman.com/) or any browser with an [extension](https://chrome.google.com/webstore/detail/jsonview/chklaanhfefbnpoihckbnefhakgolnmc?hl=en) for viewing JSON. Note that `deploy-local` is run in the foreground continually. To stop, you can issue a kill signal (`Ctrl + c`).

If you change your Typescript code, then you should either do `npm run build` or make sure `npm run watch` is also being run in another terminal.

2. In your local Wordpress installation, set the following variables in `wp-config.php`:
```
/* Webonary Cloud Backend */
define('WEBONARY_CLOUD_API_URL', 'https://cloud-api.webonary.work/v1/');
define('WEBONARY_CLOUD_FILE_URL', 'https://s3.us-east-2.amazonaws.com/cloud-storage.webonary.work/');
```

This will allow your local Wordpress to access test data in `webonary.work`.

To import dictionary data to `webonary.work`, set the following environment variable before running FLex:
```
WEBONARYSERVER=webonary.work
```

3. More to come... (how to set up Dockerized version of MongoDb to store dictionary data)

## Integration Testing
1. To simulate loading of test data into Mongo from FLex, you can run `post-legacy.ts` script found in the `tools` directory.
   1. Testing should not be done against the live [Webonary site](https://www.webonary.org), so make sure to set variables in `.env` pertaining to Webonary and your API custom domain name. During development set `DEPLOY_ENV=dev` in your `.env` file to deploy to your development stack. Deployment to the `live` stack should be done through CI/CD with `DEPLOY_ENV=live`.
   2. Obtain a dictionary data zip file produced by FLex. This zip file should be unzipped and stored as a subdirectory in `tools` using the dictionary name as its subdirectory name.
   3. This subdirectory should contain the following files and directories:
      1. configured.xhtml
      2. configured.css
      3. ProjectDictionaryOverrides.css
      4. AudioVisual directory (optional)
      5. pictures directory (optional)

2. Make sure `.env` file in your root directory is set up with the Webonary username and password for that dictionary. WCA uses Webonary's Wordpress username and password for authorization (using http basic authentication).

3. To load the entire dictionary, run `npm run post-legacy dictionary_name configured.xhtml` where dictionary_name is the dictionary site name in Webonary.
   1. To limit the number of entries loaded, you can pass in a number as the final argument. For example, to load only the first 10 items, do `npm run post-legacy dictionary_name configured.xhtml 10`.
   2. Note that the post-legacy script was written to accommodate only a few model dictionaries, such as
      1. spanish-englishfooddictionary (small sample, but no images and sound files)
      2. moore (large dictionary with both images and sound files)
      3. marwari (Hindic dictionary with some images and sounds)
   3. Once the data is loaded, you can test various view, browse, and search APIs using tools like [curl](https://curl.haxx.se/) or [postman](https://www.postman.com/) or any browser with an [extension](https://chrome.google.com/webstore/detail/jsonview/chklaanhfefbnpoihckbnefhakgolnmc?hl=en) for viewing JSON.

## Other Useful Commands
 * `cdk diff`        compare deployed stack with current state
 * `cdk synth`       emits the synthesized CloudFormation template

## Roadmap
More to come...
