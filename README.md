# Webonary

### Getting started

After cloning this repository, run the following commands in the terminal.

```bash
composer install
cd wordpress-develop
npm install
npx update-browserslist-db@latest
npm run build:dev
cd ..
```

### PHP Unit Testing:
#### 1. Create the test database
```bash
mysql --defaults-file=~/.mysql/my.local.conf -Bse "DROP SCHEMA IF EXISTS wp_webonary_test;"
mysql --defaults-file=~/.mysql/my.local.conf -Bse "CREATE SCHEMA IF NOT EXISTS wp_webonary_test DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;"
```

### Original TeamCity PR task
```bash
curl -o composer.phar https://getcomposer.org/composer-2.phar
php composer.phar install --verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader
php ./wp-resources/localizations/recompile-all-sil-dictionary.php
```

### New TeamCity PR task
```bash
# download composer and install composer dependencies
curl -o composer.phar https://getcomposer.org/composer-2.phar
COMPOSER=composer-team-city.json php composer.phar install --verbose --no-progress --no-interaction --optimize-autoloader

# compile the localization files
php ./localizations/recompile-all-sil-dictionary.php

# run PHP Unit tests
./vendor/bin/phpunit --testsuite=webonary-tests --configuration="./phpunit.xml"
```
