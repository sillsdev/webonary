#!/bin/bash

usage() { echo "Usage: $0 [-r <dump|restore|refresh>] [-d <restoreDir>] [-e <webonary-work|webonary-org|other_env>]" 1>&2; exit 1; }

while getopts r:d:e: flag
do
  case "${flag}" in
    r) request=${OPTARG};;
    d) directory=${OPTARG};;
    e) env=${OPTARG};;
    *) usage;;
  esac
done

now=$(date -u +"%FT%H%MZ")
dumpDir="${request}_${now}"

if [ "$request" = "refresh" ]
then
  echo "$now refreshing webonary.work from webonary.org ..."

  echo "$now dumping webonary.work to $dumpDir ..."
  mongodump --config webonary-work.yaml --out $dumpDir

  echo "$now dumping webonary.org to $dumpDir ..."
  mongodump --config webonary-org.yaml --out $dumpDir

  echo "$now restoring webonary.org to webonary.work ..."
  mongorestore --config webonary-work.yaml --drop ${dumpDir}/webonary
  exit 0
fi

if [ "$env" = "" ]
then 
  echo "Error: You must specify your mongo env that corresponds to env.yaml config file!"
  usage
  exit 1
fi

if [ "$request" = "dump" ]
then 
  echo "$now dumping from $env to $dumpDir ..."
  mongodump --config ${env}.yaml --out $dumpDir
  exit 0
fi

if [ "$request" = "restore" ]
then
  if [ "$env" = "webonary-org" ]
  then
    echo "Error: You cannot restore to webonary-org using this script. Too dangerous!!!"
    exit 1
  fi

  if [ "$directory" = "" ]
  then
    echo "Error: -d (directory) parameter must be specified when requesting a restore!"
    usage
    exit 1
  fi

  echo "$now restoring to $env using $directory ..."
  mongorestore --config ${env}.yaml --drop $directory
fi

echo "Error: Uh oh! Don't know what to do with --request $request ..."
usage
exit 1
