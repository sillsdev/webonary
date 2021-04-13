# Instructions for WordPress Developers

Installation of WordPress and most plugins is handled with Composer. If
you aren't familiar with Composer, you can get instructions on installation
and how to use it here (<https://getcomposer.org/>).

### Getting Started

The first steps are to clone the repository and install WordPress. The
recommended location is a directory accessible by Apache. Possibilities
include, but are not limited to `/var/www/sites` or `/var/www/projects`.

```bash
mkdir -p /var/www/sites/
cd /var/www/sites/
git clone https://github.com/sillsdev/webonary.git
cd webonary
composer install
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

*NOTE: this process may take several hours.*

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
   mkdir -p /var/www/sites/webonary.work/current/wordpress/wp-content/wflogs
   touch /var/www/sites/webonary.work/current/wordpress/wp-content/wflogs/rules.php
   wp eval-file updateDataLive2Work.php --path='/var/www/sites/webonary.work/current/wordpress'
   wp cache flush --path='/var/www/sites/webonary.work/current/wordpress'
   ```
5. That's it, you're finished!
