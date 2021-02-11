SIL Theme for webonary.org
==========================

## Installation ##

Having cloned the repo install the theme dependencies using composer  and bower dependencies.

```
cd htdocs/wp/wp-content/themes/web-webonary-sil
composer install
bower install
```

We use gulp for our build runner, so if you want to use that you'll need to install the node dependencies also.

    npm install

## Building ##

This theme uses less which can be compiled using gulp.

    gulp less

See the gulpfile.json for further details.  There's also a watch target which will watch the less files and build when one is changed.

