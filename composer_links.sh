#!/usr/bin/env bash

thisDir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"


# uploads directory
if [[ -d "${thisDir}/shared/uploads" ]]; then
  ln -sfn "${thisDir}/shared/uploads/" "${thisDir}/wordpress/wp-content/uploads"
fi

if [[ -d "${thisDir}/shared/blogs.dir" ]]; then
  ln -sfn "${thisDir}/shared/blogs.dir/" "${thisDir}/wordpress/wp-content/blogs.dir"
fi

# mu-plugins directory
ln -sfn "${thisDir}/wp-resources/mu-plugins/" "${thisDir}/wordpress/wp-content/mu-plugins"


# link wp-config
FILE="${thisDir}/shared/config/wp-config.php"
if [[ -f "$FILE" ]]; then
  ln -sfn "${FILE}" "${thisDir}"/wordpress/wp-config.php
fi


# link .htaccess
FILE="${thisDir}/shared/config/.htaccess"
if [[ -f "$FILE" ]]; then
  ln -sfn "${FILE}" "${thisDir}"/wordpress/.htaccess
fi


# set default favicon
FILE="${thisDir}/wordpress/wp-content/plugins/shockingly-simple-favicon/default/favicon.ico"
if [[ -f "$FILE" ]]; then
  rm -f "$FILE"
fi
ln -sfn "${thisDir}/wp-resources/favicon.ico" "${FILE}"


# link files in the web root directory
FILES="${thisDir}/wp-resources/*.*"
for f in $FILES
do
  fn=$(basename "$f")
  ln -sfn "${f}" "${thisDir}/wordpress/${fn}"
done


# remove stray wp-content directory
if [[ -d "${thisDir}/wordpress/wp-content/wp-content" ]]; then
  rm -rf "${thisDir}/wordpress/wp-content/wp-content"
fi


# link plugins directories
DIRS="${thisDir}/wp-resources/plugins/*/"
for d in $DIRS
do
  fn=$(basename "$d")
  target="${thisDir}/wordpress/wp-content/plugins/${fn}"
  ln -sfn "${d}" "${target}"
done


# link themes directories
DIRS="${thisDir}/wp-resources/themes/*/"
for d in $DIRS
do
  fn=$(basename "$d")
  target="${thisDir}/wordpress/wp-content/themes/${fn}"
  ln -sfn "${d}" "${target}"
done
