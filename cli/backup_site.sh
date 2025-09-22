#!/usr/bin/env bash

siteID=${1:-missing}
dumpDir="/tmp/data-transfer/"

echo "Creating ${dumpDir}"
rm -rf "${dumpDir}"
mkdir -p "${dumpDir}"
rm -f "/tmp/webonary-tables.txt"

if [[ "${siteID}" == "all" ]]; then
  sql="SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'webonary';"
else
  sql="SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'webonary' AND TABLE_NAME NOT REGEXP 'wp_[0-9]+_' UNION SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'webonary' AND TABLE_NAME LIKE '%\_${siteID}\_%';"
fi

mysql --defaults-file=~/.mysql/my.local.conf -D webonary -Bse "${sql}" > /tmp/webonary-tables.txt

while IFS="" read -r line; do
  echo "$line"
  mysqldump --defaults-file=~/.mysql/my.local.conf webonary "${line}" > "${dumpDir}backup_${line}.sql"
  echo "Cleaning up ${line}"
  sed -i -E 's/\/\*\!50017\sDEFINER=.+`@`(%|localhost)`\*\// /g' "${dumpDir}backup_${line}.sql"
  sed -i -E 's/\sDEFINER=.+`@`(%|localhost)`\s/ /g' "${dumpDir}backup_${line}.sql"
  sed -i -E 's/,?NO_AUTO_CREATE_USER//g' "${dumpDir}backup_${line}.sql"
  sed -i -E 's/https:\/\/www.webonary.org/http:\/\/webonary.localhost/g' "${dumpDir}backup_${line}.sql"
  sed -i -E 's/https:\/\/webonary.org/http:\/\/webonary.localhost/g' "${dumpDir}backup_${line}.sql"
  sed -i -E 's/www.webonary.org/webonary.localhost/g' "${dumpDir}backup_${line}.sql"
  sed -i -E 's/webonary.org/webonary.localhost/g' "${dumpDir}backup_${line}.sql"

done < "/tmp/webonary-tables.txt"

rm -f "/tmp/webonary-tables.txt"

echo "Finished"
