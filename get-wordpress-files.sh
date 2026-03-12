#!/usr/bin/env bash

thisDir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
wordpress_dir="${thisDir}/wordpress-develop"

if [[ ! -d "${wordpress_dir}" ]]; then
  #git clone --depth 1 -b trunk git://develop.git.wordpress.org/ "${wordpress_dir}"
  git clone --depth 1 -b 6.9.4 git://develop.git.wordpress.org/ "${wordpress_dir}"
else
  cd "${wordpress_dir}"
  git pull
  cd "${thisDir}"
fi
