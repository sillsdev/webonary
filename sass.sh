#!/usr/bin/env bash

echo "Compiling SASS files"

thisDir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

declare -a files=("style"
                  )

for file in "${files[@]}"
do
   printf "   Compiling %s.scss... " "${file}"

   "${thisDir}/node_modules/.bin/sass" "${thisDir}/wp-resources/themes/webonary-2/scss/${file}.scss" "${thisDir}/wp-resources/themes/webonary-2/css/${file}.css"

   printf "compressing... "
   "${thisDir}/node_modules/.bin/sass" --style=compressed --no-source-map "${thisDir}/wp-resources/themes/webonary-2/scss/${file}.scss" "${thisDir}/wp-resources/themes/webonary-2/css/${file}.min.css"

#   printf "removing BOM... "
#   sed -i '1s/^\xEF\xBB\xBF//' "${thisDir}/sass/compiled/${file}.css"
#   sed -i '1s/^@charset "UTF-8";//' "${thisDir}/sass/compiled/${file}.css"
#   sed -i '1s/^\xEF\xBB\xBF//' "${thisDir}/sass/compiled/${file}.min.css"
#   sed -i '1s/^@charset "UTF-8";//' "${thisDir}/sass/compiled/${file}.min.css"
   printf "finished.\n"
done

echo "Finished compiling SASS files"
