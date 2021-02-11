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
