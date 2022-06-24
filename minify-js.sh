#!/bin/bash

echo "Compiling typescript files"

thisDir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

declare -a files=("wp-resources/themes/webonary-2/lib/jquery.cycle.all"
                  )

for file in "${files[@]}"
do
   printf "   Minifying %s.js... " "${file}"
   "${thisDir}/node_modules/.bin/terser" "${thisDir}/${file}.js" -o "${thisDir}/${file}.min.js" --compress --mangle

   printf "finished.\n"
done

echo "Finished compiling typescript files"
