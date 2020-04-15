=== WordPress phpinfo() ===
Contributors: MrFlannagan
Plugin URI: https://whoischris.com/
Tags: simple, php, admin, phpinfo, debugging, configuration, server, support, troubleshooting, email, version, copy
Requires at least: 4.0.0
Tested up to: 4.9.1
Stable tag: 16.3

It's important for a non technical administrator to be able to diagnose server related problems in WordPress and email the information through this plugin.

== Description ==

It's important for a non technical administrator to be able to diagnose server related problems in WordPress but also rapidly retrieve feedback regarding their web server.
This simple plugin adds an option to an administrator's Settings menu which displays standard phpinfo() feedback details to the user.

You can then copy or email it directly from within the settings page to a support agent.

This a very simple script, designed to help the non technical user get immediate feedback about their blog.

This plugin is maintained by [Chris Flannagan](https://whoischris.com) or you can find him on Twitter at [@ChrisFlanny](https://twitter.com/ChrisFlanny)


== Updates ==

16.3 You can now click a button to copy a plain text version of the phpinfo to your clipboard.  Then you can paste in an email or anywhere you like.

Updates to the plugin will be posted here by the author [Chris Flannagan](https://whoischris.com)

== Screenshots ==

1. PHP Info for WordPress

== Installation ==

To install the plugin, please upload the folder to your plugins folder and active the plugin.

== Frequently Asked Questions ==

= Where is the information displayed? =

In your Settings menu.

= Can I call it in a theme? =

To call the function from a theme include the code wordpressphpinfo(); in your theme code.

== Donations ==

[Donate Here! :D](https://whoischris.com/donate)


== Change Log ==

16.3
* Copy phpinfo in plain text with button click

16.1
* Changed the method of pulling tables out of the html document created by phpinfo().  Works better, cleaner.

16
* cleaned up interface for emails
* improved emails functionality
* improved UI
* Completely recoded with much better/safer programming practices and WP standards

15
* tested to WordPress 4.8
* add email phpinfo form to settings page

14.12
* tested to WordPress 4.1
* streamlined the output screen to display more raw data

3.5
* updated WP admin screens
* tested to WordPress 3.2

3.1
* updated WP admin screens
* tested to WordPress 3.1

3.0.01
* converted plugin to utilize CR common library
* altered phpinfo() output to avoid theme conflicts

2.0.1
* documentation updates

2.0.0
* efficiency updates, repaired broken link

1.1.2
* updated update functions

1.1.0
* 2.8 compatibility fixes

1.0.3
* 2.8 compatibility fixes

1.0.0
* upgraded admin menus
* moved pages to settings panel

0.2.1 (2009-05-07)
* documentation modification

0.2.0 (2009-05-07)
* removed a header call which was causing some problems on isolated servers.

0.1.3 (2009-03-26)
* Happy Birthday to me
* Fixed a link in the readme.txt file

0.1.2 (2009-03-16)
Added the change log


== Upgrade Notice ==

14.12
* tested to WordPress 4.1
* streamlined the output screen to display more raw data
