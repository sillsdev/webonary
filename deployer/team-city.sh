#!/usr/bin/env bash


# color settings for error and success messages
RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m' # No Color


# the number of old releases to keep
OLD_TO_KEEP=5


# determine if this is testing or production deploy
STAGE=${1:-testing}
if [[ "${STAGE}" == "production" ]]; then
  SERVER="sysops.webonary.org"
  SITE_DIR="/var/www/sites/webonary.org"
else
  SERVER="sysops.webonary.work"
  SITE_DIR="/var/www/sites/webonary.work"
fi


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
DIRS="$( rsync --list-only -e 'ssh' ${SERVER}:${SITE_DIR}/releases/ )"
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
rm -rf "${SITE_DIR}"
mkdir -p "${SITE_DIR}/shared/uploads/"
mkdir -p "${SITE_DIR}/shared/blogs.dir/"
touch "${SITE_DIR}/shared/.htaccess"
touch "${SITE_DIR}/shared/wp-config.php"
touch "${SITE_DIR}/shared/wp-cache-config.php"


# make the release directory
RELEASE_DIR="${SITE_DIR}/releases/${NEXT_DIR}"
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
cp "${PROJ_DIR}/wp-resources/favicon.ico" "${RELEASE_DIR}/wordpress/favicon.ico"


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
ln -sf "${SITE_DIR}/shared/.htaccess" "${RELEASE_DIR}/wordpress/.htaccess"
ln -sf "${SITE_DIR}/shared/wp-config.php" "${RELEASE_DIR}/wordpress/wp-config.php"
ln -sf "${SITE_DIR}/shared/wp-cache-config.php" "${RELEASE_DIR}/wordpress/wp-content/wp-cache-config.php"

ln -sfn "${SITE_DIR}/shared/uploads/" "${RELEASE_DIR}/wordpress/wp-content/uploads"
ln -sfn "${SITE_DIR}/shared/blogs.dir/" "${RELEASE_DIR}/wordpress/wp-content/blogs.dir"


# link from the new release to current
ln -sfn "${RELEASE_DIR}" "${SITE_DIR}/current"


# finished building the site
echo "Finished building the site."


# copy the files to the server
echo "Copying files to the server."
rsync -az --chmod=D2775,F664 -e 'ssh' "${RELEASE_DIR}/." "${SERVER}:${RELEASE_DIR}"
EXIT_CODE=$?
if [[ $EXIT_CODE -ne 0 ]]; then
  echo -e "\n============\n${RED}COPY FAILED!${NC}\n============\n"
  exit 1
fi
echo "Finished copying the site."


# make the new release the current one
rsync -az --chmod=D2775,F664 -e 'ssh' "${SITE_DIR}/current" "${SERVER}:${SITE_DIR}"
EXIT_CODE=$?
if [[ $EXIT_CODE -ne 0 ]]; then
  echo -e "\n==============\n${RED}DEPLOY FAILED!${NC}\n==============\n"
  exit 1
fi
echo "Finished deploying the site."


# remove an old release if we exceeded the desired threshold
# NOTE: rsync can empty the directory but not remove it, sftp can remove it but not empty it
LEN=${#SORTED[@]}
while [[ $LEN -gt 2 && $LEN -gt $OLD_TO_KEEP ]]
do
  echo "Removing old release ${SORTED[0]}."

  # empty the directory on the server
  mkdir -p "${SITE_DIR}/releases/${SORTED[0]}"
  rsync -ar --delete --include='*' "${SITE_DIR}/releases/${SORTED[0]}" "${SERVER}:${SITE_DIR}/releases"

  # remove the directory we want to delete
  sftp "${SERVER}" <<< $"rmdir ${SITE_DIR}/releases/${SORTED[0]}"

  # remove the directory from the list
  SORTED=("${SORTED[@]:1}")
  LEN=${#SORTED[@]}
done


# clean up
echo "Cleaning up."
rm -rf "${SITE_DIR}"


# successfully deployed
echo -e "\n========\n${GREEN}SUCCESS!${NC}\n========\n"
