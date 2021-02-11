=== Disable Site Delete ===
Contributors: sbrajesh,buddydev
Tags: multisite, blog,delete
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Disable Site Delete plugin completely disables the site/blog deletion by a non network administrator

== Description ==

Disable Site Delete plugin only allows network administrators to delete a blog/site on a WordPress Multisite network. It does not allow blog owners to delete their blogs.

It works by doing that in 3 steps:-

*   Removes the delete Site link from tools menu for non network admins
*   Breaks out of wpmu_delete_blog function to avoid deletion of blog
*   hacks around option to avoid sending the delete confirmation mail to blog owner

Need more details, please visit this post on [BuddyDev](http://buddydev.com/wordpress-multisite/introducing-disable-site-delete-plugin-for-wordpress-multisite-based-networks/ "Introducing Disable Site Delete plugin") 
== Installation ==


1. Download `disable-site-delete.zip` and extract(unzip)
1. Upload `disable-site-delete` to the `/wp-content/plugins/` directory
1. Network Activate the plugin through the 'My Sites->Network Admin->Plugins' menu in WordPress Multisite
1. have fun!

== Frequently Asked Questions ==

= Does it completely removes the capability to delete blog for blog admins/owners =

That's right. Only network administrator can delete a blog.

= How to localize =
Please look into the 'languages' directory of this plugin. you will find the po file. Just translate it and save there as your_locale.mo(e.f en_US.mo)

= Is there an alternative? =
Yes, there is a plugin by Ryan Hellyer [Delete Delete Site](http://wordpress.org/plugins/delete-delete-site/ "Delete Delete Site") but that only removes the menu link and does not actually prevent users from doing it completely(try typing the url and you will know what I mean).

= I have more Questions? =
We will love to hear your feedback at [BuddyDev](http://BuddyDev.com/ "We help you to build your WordPress, BuddyPress based social network")
== Screenshots ==

1. No Delete Menu for Non Network Administrator screenshot-1.png. 
2. Here is the message shown to the blog owner if they somehow manage to open the link screenshot-2.png

== Changelog ==

= 1.0 =
Initial release

