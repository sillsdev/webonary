# Monorepo of everything Webonary!

###  How to restore a backup locally
```
mysql -uwebonary -p -A --default-character-set=utf8mb4 webonary
mysql> SET names 'utf8';
mysql> SOURCE webonary.sql;
mysql> UPDATE wp_options SET option_value = replace(option_value, 'webonary.org', 'webonary.work') WHERE option_name = 'home' OR option_name = 'siteurl';
mysql> UPDATE wp_posts SET guid = replace(guid, 'webonary.org','webonary.work');
mysql> UPDATE wp_posts SET post_content = replace(post_content, 'webonary.org', 'webonary.work');
mysql> UPDATE wp_blogs SET domain = replace(domain, 'webonary.org', 'webonary.work');
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
