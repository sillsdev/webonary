#!/usr/bin/env bash

thisDir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# link wp-tests-config
FILE="${thisDir}/resources/wp-tests-config.php"
if [[ -f "$FILE" ]]; then
  ln -sfn "${FILE}" "${thisDir}/wordpress-develop/wp-tests-config.php"
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
