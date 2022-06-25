export function deployEnvPrefix(): string {
  // for historical reasons, dev env will not have a prefix attached
  const deployEnv = process.env.DEPLOY_ENV;
  switch (deployEnv) {
    case undefined:
    case 'dev':
      return '';
    default:
      return `${deployEnv}-`;
  }
}

export function envSpecific(logicalName: string): string {
  return `${deployEnvPrefix()}${logicalName}`;
}
