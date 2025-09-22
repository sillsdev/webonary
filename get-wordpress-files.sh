#!/usr/bin/env bash

wordpress_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/wordpress-develop"

if [[ ! -d "${wordpress_dir}" ]]; then
  git clone --depth 1 -b trunk git://develop.git.wordpress.org/ "${wordpress_dir}"
fi
