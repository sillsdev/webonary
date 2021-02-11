# Monorepo of everything Webonary!

###  How to restore a backup locally

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
rsync -avz --chmod=D2775,F664 -e 'ssh -i ~/.ssh/id_rsa -o IdentitiesOnly=yes' your_name@server_name.org:/var/www/webonary.org/htdocs/wp/wp-content/blogs.dir /var/www/projects/webonary2/wordpress/wp-content
```
