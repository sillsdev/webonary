#!/usr/bin/env bash

siteID=${1:-missing}
if [[ "${siteID}" == "missing" ]]; then
  read -p "Enter the site number: " siteID
else
  read -p "Enter the site number [${siteID}]: " siteID
fi

if [[ -f "${thisDir}/config/data-server" ]]
then
	. "${thisDir}/config/data-server"
fi

dumpDir="/tmp/data-transfer/"

echo "Creating ${dumpDir}"
rm -rf "${dumpDir}"
mkdir -p "${dumpDir}"

echo "Backing up..."
ssh -A "${dataServer}" "./backup_site.sh ${siteID}"

echo "Copying from the server"
rsync -az --chmod=D2775,F775 -e "ssh -A" "${dataServer}:${dumpDir}*.sql" "${dumpDir}"

FILES="${dumpDir}*.sql"

for f in $FILES
do
    echo "Restoring ${f}"
    mysql --defaults-file=~/.mysql/my.local.conf --default-character-set=utf8 -D webonary < "${f}"
done

echo "Cleaning up server"
ssh -A "${dataServer}" "rm -rf ${dumpDir}"

echo "Setting up the blogs table"
sql="DELETE FROM webonary.wp_blogs
WHERE blog_id > 1
  AND blog_id NOT IN (
    SELECT REGEXP_REPLACE(TABLE_NAME, 'wp_([0-9]+)_posts', '\\\\1')
    FROM INFORMATION_SCHEMA.TABLES
    WHERE TABLE_SCHEMA = 'webonary'
      AND TABLE_NAME REGEXP 'wp_[0-9]+_posts'
);"
mysql --defaults-file=~/.mysql/my.local.conf -D webonary -Bse "${sql}"

echo "Finished"
