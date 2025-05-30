# Instructions for WordPress Developers

First, [install PHP 7.3 on your machine](https://kinsta.com/blog/install-php/).

Installation of WordPress and most plugins is handled with [Composer](https://getcomposer.org/). If
you aren't familiar with Composer, you can get instructions on installation
and how to use it [here](https://getcomposer.org/doc/00-intro.md).

## Getting Started (Webonary cloud api integration development only)

If you are working on Webonary integration with the Cloud API backend only, you can use
[wp-env](https://www.npmjs.com/package/@wordpress/env)
for local development using [Docker](https://www.docker.com/).

To start, clone this full repo and install [npm](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm) packages and install Wordpress plugins through [composer](https://getcomposer.org/).

```bash
git clone https://github.com/sillsdev/webonary.git

cd webonary

npm install

composer install
```

Then, copy sample [wp-env.json](.wp-env.json.sample) configuration file and modify it to point to a valid Webonary Cloud API environment and a test dictionary.

```bash
cp ../.wp-env.json.sample .wp-env.json
```

The local environment will be available at http://localhost:8888 (Username: admin, Password: password) once you start it by:

```bash
npx wp-env start
```

See [here for more instructions on wp-env](https://www.npmjs.com/package/@wordpress/env)

## Getting Started (full local WordPress development)

The first steps are to clone the repository and install WordPress. The
recommended location is a directory accessible by Apache. Possibilities
include, but are not limited to `/var/www/sites` or `/var/www/projects`.

```bash
mkdir -p /var/www/sites/
cd /var/www/sites/
git clone https://github.com/sillsdev/webonary.git
cd webonary
composer install
npm install
```

Next, make a copy of `shared/config/wp-config-sample.php` and name it
`shared/config/wp-config.php`. This will be the configuration file used
by Webonary. The last few lines of the file have been modified to function
correctly in this modified directory structure. In `shared/config/wp-config.php`
set the database connection information.

```bash
cp shared/config/wp-config-sample.php shared/config/wp-config.php
```

Add the following line to `/etc/hosts`

```bash
127.0.0.1   webonary.localhost
```

Add the following Apache virtual host. On Ubuntu the file is `/etc/apache2/sites-available/000-default.conf`

```bash
<VirtualHost *:80>
    ServerName webonary.localhost
    ServerAdmin webmaster@localhost

    DocumentRoot /var/www/sites/webonary/wordpress

    ErrorLog ${APACHE_LOG_DIR}/webonary_error.log
    CustomLog ${APACHE_LOG_DIR}/webonary_access.log combined

    <Directory "/var/www/sites/webonary/wordpress/">
        Options Indexes FollowSymLinks
        AllowOverride All
        Order allow,deny
        Allow from all
        Require all granted
    </Directory>
</VirtualHost>
```

Finally, restart Apache:

```bash
sudo service apache2 restart
```

### Install WP CLI

See <https://wp-cli.org/>

```bash
cd ~/
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
php wp-cli.phar --info
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp
```

### Deploying to webonary.work

You will need to add webonary.work to your `~/.ssh/config` file.

```
Host sysops.webonary.work
  User your_user_name
  IdentityFile ~/.ssh/your_private_key
  IdentitiesOnly yes
  Port 22
```

Once that is done, run the following command in the repository root. You
may need to install `make`.

```bash
make test
```

### Restore MySQL backup to webonary.work

_NOTE: this process may take several hours._

For this to work, you will need to create a MySQL config file `~/.mysql/my.local.conf` and put this in it:

```ini
[mysql]
user=your_user
password="your_password"
host=localhost
protocol=tcp
port=3306

[mysqldump]
user=your_user
password="your_password"
host=localhost
protocol=tcp
port=3306
```

1. Download a backup from <https://s3.amazonaws.com/backups.languagetechnology.org/webonary.org/mysql/daily/webonary/>
2. Rename the downloaded file to `webonary.sql.gz`
3. Transfer the file to webonary.work:
   ```
   rsync -avz --chmod=D2775,F664 -e 'ssh' ~/Downloads/webonary.sql.gz sysops.webonary.work:~/webonary.sql.gz
   ```
4. On webonary.work run these commands:

   ```
   cd ~/
   rm -f webonary.sql
   gunzip webonary.sql.gz
   mysql --defaults-file=~/.mysql/my.local.conf -A --default-character-set=utf8mb4 webonary
   mysql> SOURCE webonary.sql;
   mysql> UPDATE wp_blogs SET domain = replace(domain, 'webonary.org', 'webonary.work');
   mysql> quit

   mkdir -p /var/www/sites/webonary/current/wordpress/wp-content/wflogs
   touch /var/www/sites/webonary/current/wordpress/wp-content/wflogs/rules.php
   wp eval-file updateDataLive2Work.php --path='/var/www/sites/webonary/current/wordpress'
   wp cache flush --path='/var/www/sites/webonary/current/wordpress'
   ```

5. That's it, you're finished!

### Install MongoDB Shell
```bash
wget -qO- https://www.mongodb.org/static/pgp/server-6.0.asc | sudo tee /etc/apt/trusted.gpg.d/server-6.0.asc
echo "deb [ arch=amd64,arm64 ] https://repo.mongodb.org/apt/ubuntu jammy/mongodb-org/6.0 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-6.0.list
sudo apt update
sudo apt install mongodb-mongosh
mongosh --version
```

### Install MongoDB tools

Download from [https://www.mongodb.com/try/download/database-tools]

Instructions, [https://www.mongodb.com/docs/database-tools/installation/installation-linux/]

```bash
mongoexport --config=/home/phil/.mongo/webonary_org.yml --db=webonary --collection=webonaryDictionaries --out=/home/phil/dumps/dictionaries.json --query='{ "_id": "test-stutzman" }'
# mongoexport --config=/home/phil/.mongo/webonary_org.yml --db=webonary --collection=webonaryReversals --out=/home/phil/dumps/reversals.json --query='{ "dictionaryId": "test-stutzman" }'
mongoexport --config=/home/phil/.mongo/webonary_org.yml --db=webonary --collection=webonaryEntries_test-stutzman --out=/home/phil/dumps/entries.json
mongoexport --config=/home/phil/.mongo/webonary_org.yml --db=webonary --collection=webonaryReversals --out=/home/phil/dumps/reversals.json


# mongoimport --config=/home/phil/.mongo/webonary_work.yml --db=webonary-work --collection=webonaryReversals --mode=upsert --file=/home/phil/dumps/reversals.json
mongoimport --config=/home/phil/.mongo/webonary_work.yml --db=webonary-work --collection=webonaryDictionaries --mode=upsert --file=/home/phil/dumps/dictionaries.json
mongoimport --config=/home/phil/.mongo/webonary_work.yml --db=webonary-work --collection=webonaryEntries_test-stutzman --drop --mode=upsert --file=/home/phil/dumps/entries.json
mongoimport --config=/home/phil/.mongo/webonary_work.yml --db=webonary-work --collection=webonaryReversals --drop --mode=upsert --file=/home/phil/dumps/reversals.json
```

### Copy S3 folder from .org to .work
```bash
aws s3 cp --recursive --profile webonary s3://cloud-storage.webonary.org/lietuviukalba-espanol /home/phil/Downloads/webonary
aws s3 cp --recursive --profile webonary /home/phil/Downloads/webonary s3://cloud-storage.webonary.work/lietuviukalba-espanol
```

### Install redis on the server
See <https://www.digitalocean.com/community/tutorials/how-to-install-and-secure-redis-on-ubuntu-22-04>
```bash
sudo apt update
sudo apt install redis-server
sudo nano /etc/redis/redis.conf
# set the `supervised` value to `systemd`
sudo systemctl restart redis.service
sudo systemctl status redis
redis-cli
> ping
# response should be PONG
exit
```

### To drop all "BACKUP" tables

```bash
sudo mysql -A

USE webonary;

SELECT CONCAT('DROP TABLE ', TABLE_SCHEMA, '.', TABLE_NAME, ';')
FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME LIKE '%\_BACKUP\_%' AND TABLE_SCHEMA = 'webonary'
INTO OUTFILE '/tmp/drop.sql';

SOURCE /tmp/drop.sql;

quit
```


### How to restore a site (site 433 in this example) from a backup

1 - On webonary.org server:
```bash
sudo -i
cd /mnt/backups/mysql/daily/webonary
ls -lh
cp webonary_2024-12-20_06h25m.Friday.sql.gz /tmp/webonary.sql.gz
chown your_username:root /tmp/webonary.sql.gz
exit
```

2 - On your computer:
```bash
rsync -avz --chmod=D2775,F664 -e 'ssh' sysops.webonary.org:/tmp/webonary.sql.gz ~/webonary.sql.gz
cd ~/
rm -f webonary.sql
gunzip webonary.sql.gz
mysql --defaults-file=~/.mysql/my.local.conf -A --default-character-set=utf8mb4 webonary

mysql> USE webonary;
mysql> SOURCE webonary.sql;
mysql> quit

mysqldump --defaults-file="~/.mysql/my.local.conf" webonary $(mysql --defaults-file="~/.mysql/my.local.conf" -D webonary -Bse "show tables like 'wp\_433_%'") > backup_433.sql
rsync -avz --chmod=D2775,F664 -e 'ssh' ~/backup_433.sql sysops.webonary.org:/home/your_username/backup_433.sql
```

3 - On webonary.org server:
```bash
cd ~/

sudo mysql -A

mysql> USE webonary;
mysql> SOURCE backup_433.sql;
mysql> quit
```

4 - On your computer. Get the supporting files from webonary.work
```bash
rsync -avz --chmod=D2775,F664 -e 'ssh -i ~/.ssh/id_rsa -o IdentitiesOnly=yes' sysops.webonary.work:/var/www/sites/webonary/shared/blogs.dir/433 /var/www/projects/webonary/shared/blogs.dir/
```

5 - On your computer. Send supporting files to webonary.org
```bash
rsync -avz --chmod=D2775,F664 -e 'ssh -i ~/.ssh/id_rsa -o IdentitiesOnly=yes' /var/www/projects/webonary/shared/blogs.dir/433 sysops.webonary.org:/var/www/sites/webonary/shared/blogs.dir/
```
