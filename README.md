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
