=== Disable Real MIME Check ===
Contributors: SergeyBiryukov
Tags: upload, media, mime
Requires at least: 4.7.1
Tested up to: 4.7.2
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Restores the ability to upload non-image files in WordPress 4.7.1 and 4.7.2.

== Description ==

With the upgrade to WordPress 4.7.1, some non-image files fail to upload on certain server setups. This will be fixed in 4.7.3, see the [Trac ticket](https://core.trac.wordpress.org/ticket/39550).

In the meantime, this plugin is a workaround that disables the recently introduced strict MIME check to restore the upload functionality.

Don't forget to remove the plugin once WordPress 4.7.3 is available!

== Installation ==

1. Upload `disable-real-mime-check` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Changelog ==

= 1.0 =
* Initial release
