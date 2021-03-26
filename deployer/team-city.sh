#!/usr/bin/env bash


RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m' # No Color


# TODO: determine if this is testing or production deploy


# get the actual path to the project directory
thisDir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJ_DIR="$( cd "$( dirname "${thisDir}" )" && pwd )"


# check that composer install has been run
if [[ ! -d "${PROJ_DIR}/vendor" ]]; then
  echo -e "${RED}ERROR: You must run 'composer install' first.${NC}"
  exit 1
fi


# get the next release directory
RELEASES=()
DIRS="$( rsync --list-only -e 'ssh' sysops.webonary.work:/var/www/sites/webonary.work/releases/ )"
while IFS=' ' read -ra vars; do
  dir_name="${vars[-1]}"

  # release directories have integer names
  re='^[0-9]+$'
  if [[ $dir_name =~ $re ]]; then

    test_val="$((dir_name + 0))"
    RELEASES[${#RELEASES[@]}]=$test_val
  fi
done <<< "$DIRS"
SORTED=( $( printf "%s\n" "${RELEASES[@]}" | sort -n ) )
NEXT_DIR="$((SORTED[-1] + 1))"


# make the shared directories and files
echo "Creating shared"
rm -rf "/var/www/sites/webonary.work"
mkdir -p "/var/www/sites/webonary.work/shared/uploads/"
mkdir -p "/var/www/sites/webonary.work/shared/blogs.dir/"
touch "/var/www/sites/webonary.work/shared/.htaccess"
touch "/var/www/sites/webonary.work/shared/wp-config.php"
touch "/var/www/sites/webonary.work/shared/wp-cache-config.php"


# make the release directory
RELEASE_DIR="/var/www/sites/webonary.work/releases/${NEXT_DIR}"
rm -rf "${RELEASE_DIR}"
mkdir -p "${RELEASE_DIR}"


# copy the vendor directory
echo "Copying the vendor directory."
cp -r "${PROJ_DIR}/vendor" "${RELEASE_DIR}/vendor"


# copy the wordpress directory
echo "Copying the wordpress directory."
cp -r "${PROJ_DIR}/wordpress" "${RELEASE_DIR}/wordpress"


# clean up unnecessary symlinks
echo "Removing unnecessary symlinks."
find "${RELEASE_DIR}/wordpress" -type l -delete
rm -f "${RELEASE_DIR}/wordpress/wp-content/plugins/shockingly-simple-favicon/default/favicon.ico"


# remove stray wp-content directory
rm -rf "${RELEASE_DIR}/wordpress/wp-content/wp-content"


# copy files from wp-resources to the corresponding location in wordpress
echo "Copying files from wp-resources."
cp -r "${PROJ_DIR}/wp-resources/mu-plugins" "${RELEASE_DIR}/wordpress/wp-content/mu-plugins"
cp "${PROJ_DIR}/wp-resources/favicon.ico" "${RELEASE_DIR}/wordpress/wp-content/plugins/shockingly-simple-favicon/default/favicon.ico"


# copy files in the web root directory
FILES="${PROJ_DIR}/wp-resources/*.*"
for f in $FILES
do
  fn=$(basename "$f")
  cp "${f}" "${RELEASE_DIR}/wordpress/${fn}"
done


# link plugins directories
echo "Copying plugin directories."
DIRS="${PROJ_DIR}/wp-resources/plugins/*/"
for d in $DIRS
do
  fn=$(basename "$d")
  target="${RELEASE_DIR}/wordpress/wp-content/plugins/${fn}"
  cp -r "${d}" "${target}"
done


# link themes directories
echo "Copying theme directories."
DIRS="${PROJ_DIR}/wp-resources/themes/*/"
for d in $DIRS
do
  fn=$(basename "$d")
  target="${RELEASE_DIR}/wordpress/wp-content/themes/${fn}"
  cp -r "${d}" "${target}"
done


# link the shared files and directories
ln -sf "${RELEASE_DIR}/wordpress/wp-content/plugins/wp-super-cache/advanced-cache.php" "${RELEASE_DIR}/wordpress/wp-content/advanced-cache.php"
ln -sf "/var/www/sites/webonary.work/shared/.htaccess" "${RELEASE_DIR}/wordpress/.htaccess"
ln -sf "/var/www/sites/webonary.work/shared/wp-config.php" "${RELEASE_DIR}/wordpress/wp-config.php"
ln -sf "/var/www/sites/webonary.work/shared/wp-cache-config.php" "${RELEASE_DIR}/wordpress/wp-content/wp-cache-config.php"

ln -sfn "/var/www/sites/webonary.work/shared/uploads/" "${RELEASE_DIR}/wordpress/wp-content/uploads"
ln -sfn "/var/www/sites/webonary.work/shared/blogs.dir/" "${RELEASE_DIR}/wordpress/wp-content/blogs.dir"


# link from the new release to current
ln -sfn "${RELEASE_DIR}" "/var/www/sites/webonary.work/current"


# finished building the site
echo "Finished building the site."


# copy the files to the server
echo "copying files to the server."
rsync -avz --chmod=D2775,F664 -e 'ssh' "${RELEASE_DIR}/." "sysops.webonary.work:${RELEASE_DIR}"
EXIT_CODE=$?
if [[ $EXIT_CODE -ne 0 ]]; then
  echo -e "${RED}COPY FAILED!${NC}"
  exit 1
fi
echo "Finished copying the site."


# make the new release the current one
rsync -avz --chmod=D2775,F664 -e 'ssh' "/var/www/sites/webonary.work/current" "sysops.webonary.work:/var/www/sites/webonary.work"
EXIT_CODE=$?
if [[ $EXIT_CODE -ne 0 ]]; then
  echo -e "${RED}DEPLOY FAILED!${NC}"
  exit 1
fi
echo "Finished deploying the site."


# remove an old release if we exceeded the desired threshold
OLD_TO_KEEP=1
LEN=${#RELEASES[@]}
if [[ $LEN -gt 2 && $LEN -gt $OLD_TO_KEEP ]]; then
  echo "Removing old release ${RELEASES[0]}."
  mkdir -p "/var/www/sites/webonary.work/releases/${RELEASES[0]}"
  rsync -arv --delete "--include=*" "/var/www/sites/webonary.work/releases/${RELEASES[0]}" "sysops.webonary.work:/var/www/sites/webonary.work/releases"
fi


# clean up
echo 'Cleaning up.'
rm -rf "/var/www/sites/webonary.work"

echo -e "\n========\n${GREEN}SUCCESS!${NC}\n========\n"
