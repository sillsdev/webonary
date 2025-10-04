#!/usr/bin/env bash

thisDir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

./vendor/bin/phpunit --testsuite=webonary-tests --configuration="${thisDir}/phpunit.xml"
exitCode=$?

exit ${exitcode}
