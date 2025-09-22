#!/usr/bin/env bash

thisDir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# link wp-config
FILE="${thisDir}/shared/config/wp-config.php"
if [[ -f "$FILE" ]]; then
  ln -sfn "${FILE}" "${thisDir}/wordpress-develop/src/wp-config.php"
fi

# link wp-cache-config
FILE="${thisDir}/shared/config/wp-cache-config.php"
if [[ -f "$FILE" ]]; then
  ln -sfn "${FILE}" "${thisDir}"/wordpress-develop/src/wp-content/wp-cache-config.php
fi

# link wp-tests-config
FILE="${thisDir}/shared/config/wp-tests-config.php"
if [[ -f "$FILE" ]]; then
  ln -sfn "${FILE}" "${thisDir}/wordpress-develop/wp-tests-config.php"
fi

# link .htaccess
FILE="${thisDir}/shared/config/.htaccess"
if [[ -f "$FILE" ]]; then
  ln -sfn "${FILE}" "${thisDir}"/wordpress-develop/src/.htaccess
fi

# set default favicon
FILE="${thisDir}/plugins/third-party/shockingly-simple-favicon/default/favicon.ico"
if [[ -f "$FILE" ]]; then
  rm -f "$FILE"
fi
ln -sfn "${thisDir}/resources/favicon.ico" "${FILE}"

# uploads directory
if [[ -d "${thisDir}/shared/uploads" ]]; then
  ln -sfn "${thisDir}/shared/uploads/" "${thisDir}/wordpress-develop/src/wp-content/uploads"
fi

if [[ -d "${thisDir}/shared/blogs.dir" ]]; then
  ln -sfn "${thisDir}/shared/blogs.dir/" "${thisDir}/wordpress-develop/src/wp-content/blogs.dir"
fi

# link plugins directories
DIRS="${thisDir}/plugins/sil/*/"
for d in $DIRS
do
  fn=$(basename "$d")
  target="${thisDir}/wordpress-develop/src/wp-content/plugins/${fn}"
  ln -sfn "${d}" "${target}"
done

DIRS="${thisDir}/plugins/third-party/*/"
for d in $DIRS
do
  fn=$(basename "$d")
  target="${thisDir}/wordpress-develop/src/wp-content/plugins/${fn}"
  ln -sfn "${d}" "${target}"
done

# mu-plugins directory
ln -sfn "${thisDir}/plugins/mu-plugins/" "${thisDir}/wordpress-develop/src/wp-content/mu-plugins"

# link themes directories
DIRS="${thisDir}/themes/*/"
for d in $DIRS
do
  fn=$(basename "$d")
  target="${thisDir}/wordpress-develop/src/wp-content/themes/${fn}"
  ln -sfn "${d}" "${target}"
done

mkdir -p "${thisDir}/shared/upgrade"
mkdir -p "${thisDir}/shared/upgrade-temp-backup"
ln -sfn "${thisDir}/shared/upgrade/" "${thisDir}/wordpress-develop/src/wp-content/upgrade"
ln -sfn "${thisDir}/shared/upgrade-temp-backup/" "${thisDir}/wordpress-develop/src/wp-content/upgrade-temp-backup"

# link files in the web root directory
FILES="${thisDir}/resources/*.*"
for f in $FILES
do
  fn=$(basename "$f")
  ln -sfn "${f}" "${thisDir}/wordpress-develop/src/${fn}"
done


# copy additional default localizations
mkdir -p "${thisDir}/wordpress-develop/src/wp-content/languages"
FILES="${thisDir}/localizations/wordpress-base/*.mo"
for f in $FILES
do
  fn=$(basename "$f")
  target="${thisDir}/wordpress-develop/src/wp-content/languages/${fn}"

  if [[ ! -f "$target" ]]; then
    cp "$f" "$target"
  fi
done
