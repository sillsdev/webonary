# Monorepo of everything Webonary!

### For developers

See [Developer README](./DEVELOPER_README.md).

### How to restore a backup locally

If you haven't done it already, copy `updateDataLive2Work.php` to your
home directory.

```
mysql -uwebonary -p -A --default-character-set=utf8mb4 webonary
mysql> SET names 'utf8';
mysql> SOURCE webonary.sql;
mysql> UPDATE wp_blogs SET domain = replace(domain, 'webonary.org', 'webonary.work');
mysql> quit
cd ~/
wp eval-file updateDataLive2Work.php --path='/var/www/sites/webonary.work/current/wordpress'
```

### How to use old submenus

The old submenu tag will display all the children of the top-level menu
listed in the `show_submenu` value. The value must be the slug (the last
value in the permalink), not the title or ID of the post.

This is the format of the short-code tag:

```
[menu show_submenu="overview"]
```

### How to use branch submenus

The branch submenu tag will display all the children of the current page.

This is the format of the short-code tag:

```
[menu show_branch=1]
```

### How to get uploaded site files

```
rsync -avz --chmod=D2775,F664 -e 'ssh -i ~/.ssh/id_rsa -o IdentitiesOnly=yes' your_name@server_name.org:/var/www/sites/webonary/shared/blogs.dir /var/www/projects/webonary2/wordpress/wp-content
```

### How to copy uploaded files from one site to another

**This copies from site 266 to site 895**

```bash
cp -R /var/www/sites/webonary/shared/blogs.dir/266/files/* /var/www/sites/webonary/shared/blogs.dir/895/files/

mysql
```

```mysql
# noinspection SqlResolve
INSERT INTO webonary.wp_895_posts (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count)
SELECT 3, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count
FROM webonary.wp_266_posts
WHERE post_type = 'attachment';

# noinspection SqlResolve
UPDATE webonary.wp_895_posts
SET guid = CONCAT('https://www.webonary.org/test-tepehuan5', SUBSTRING(guid, LOCATE('/files/', guid)))
WHERE post_type = 'attachment' AND LOCATE('/files/', guid) > 0;
```


### TeamCity PR Build Configuration

_Step 1: Composer Install_
```bash
curl -o composer.phar https://getcomposer.org/composer-2.phar
php composer.phar install --verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader
php ./wp-resources/localizations/recompile-all-sil-dictionary.php
```


### TeamCity Deploy Build Configuration

_Step 1: Composer Install_
```bash
curl -o composer.phar https://getcomposer.org/composer-2.phar
php composer.phar install --verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader
php ./wp-resources/localizations/recompile-all-sil-dictionary.php
```

_Step 2: Deploy to Testing or Production_
```bash
./deployer/team-city.sh --stage testing --user your_user_name
./deployer/team-city.sh --stage production --user your_user_name
```
