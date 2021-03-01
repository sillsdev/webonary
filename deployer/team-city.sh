#!/usr/bin/env bash

# get the actual path to the project directory
thisDir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
projDir="$( cd "$( dirname "${thisDir}" )" && pwd )"


# check that composer install has been run
if [[ ! -d "${projDir}/vendor" ]]; then
  echo "ERROR: You must run 'composer install' first."
  exit 1
fi


# place to build the site locally
echo "Creating ${tempDir}."
tempDir="/tmp/webonary/"
rm -rf "${tempDir}"
mkdir -p "${tempDir}"


# copy the vendor directory
echo "Copying the vendor directory."
cp -r "${projDir}/vendor" "${tempDir}/vendor"


# copy the wordpress directory
echo "Copying the wordpress directory."
cp -r "${projDir}/wordpress" "${tempDir}/wordpress"


# clean up unnecessary symlinks
echo "Removing unnecessary symlinks."
find "${tempDir}/wordpress" -type l -delete
rm -f "${tempDir}/wordpress/wp-content/plugins/shockingly-simple-favicon/default/favicon.ico"


# remove stray wp-content directory
rm -rf "${tempDir}/wordpress/wp-content/wp-content"


# copy files from wp-resources to the corresponding location in wordpress
echo "Copying files from wp-resources."
cp -r "${projDir}/wp-resources/mu-plugins" "${tempDir}/wordpress/wp-content/mu-plugins"
cp "${projDir}/wp-resources/favicon.ico" "${tempDir}/wordpress/wp-content/plugins/shockingly-simple-favicon/default/favicon.ico"


# copy files in the web root directory
FILES="${projDir}/wp-resources/*.*"
for f in $FILES
do
  fn=$(basename "$f")
  cp "${f}" "${tempDir}/wordpress/${fn}"
done


# link plugins directories
echo "Copying plugin directories."
DIRS="${projDir}/wp-resources/plugins/*/"
for d in $DIRS
do
  fn=$(basename "$d")
  target="${tempDir}/wordpress/wp-content/plugins/${fn}"
  cp -r "${d}" "${target}"
done


# link themes directories
echo "Copying theme directories."
DIRS="${projDir}/wp-resources/themes/*/"
for d in $DIRS
do
  fn=$(basename "$d")
  target="${tempDir}/wordpress/wp-content/themes/${fn}"
  cp -r "${d}" "${target}"
done


# finished building the site
echo "Finished building the site."


# copy the files to the server
echo "copying files to the server."
rsync -avz --chmod=D2775,F664 -e 'ssh' "${tempDir}/." sysops.webonary.work:/var/www/sites/webonary.work/releases/team-city


# create the trigger file.
# TODO: create a trigger file and copy it to the server
