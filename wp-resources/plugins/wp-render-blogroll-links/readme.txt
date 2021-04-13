=== WP Render Blogroll Links ===
Contributors: 0xTC
Tags: links, blogroll, page, template, bookmarks, descriptions, SEO
Requires at least: 2.8
Tested up to: 3.0.1
Stable tag: trunk
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6331357

Create a links page with a simple shortcode / tag. No additional templates needed.

== Description ==

Outputs your Blogroll links to a Page or Post. Add **`[wp-blogroll]`** to a Page or Post and all your Wordpress Links/Blogrolls will be rendered. This extremely simple plug-in enables you to create your own Links page without having to write a custom template.

Check the Frequently Asked Questions for detailed usage instructions.

The output can easily be styled with CSS. Each category with its links is encapsulated in a DIV  with a classname called **`"linkcat"`**. All the links are attributed with the class **`"brlink"`**.

**Additional features:**

* Order links the way you like them
* Order link categories the way you like them
* Can create selection of individual categories by name or ID(s).
* Show descriptions of the links next or under them.
* Show category descriptions under category titles.
* Hide category titles.
* Option to remove titles from categories
.
* Option to force nofollow, external or any other relationship (rel) to your links.

For advanced usage instructions please read the FAQ.

== Installation ==

1. Upload/extract the `WP-Render-Blogroll` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Place `[wp-blogroll]` in the post or page you want to use for links.

== Frequently Asked Questions ==


= How can I set the classname of the links? =

Link classnames can be set using the **`linkclass`** parameter.

**Example:**

* Set the classname for links to **`blue`**:
 * **`[wp-blogroll linkclass=blue]`**

= Can it be altered to call a specific category (or categories) in the blogroll? =

To specify one or more categories of links, add the **`catid`** or **`catname`** parameter to the tag.

**Examples:**

* For all links in the "News" category:
  * **`[wp-blogroll catname=News]`**

If your category name has a space in it, simply wrap the name in quotes:

* For all links in the "Social Media" category:
  * **`[wp-blogroll catname="Social Media"]`**

* For all links in the category ID 39:
 * **`[wp-blogroll catid=39]`**

* For all links in categories 39 and 37:
  * **`[wp-blogroll catid=39,37]`**

PS: It is not advisable to use both catid and catname simultaneously.

= I don't like category titles. How can I remove them? =

You can remove category titles by adding the **`notitle`** parameter to the tag.

**Examples:**

* For all links in all categories but no titles:
 * **`[wp-blogroll notitle=1]`**

* For all links in all categories with descriptions next to them **but no titles**:
 * **`[wp-blogroll showdesc=1 notitle=1]`**

= Is it possible to show link descriptions as text rather than just title attributes? =

To do this simply add **`showdesc=1`** to the tag. You may also want to use the **`showbrk=1`** parameter to indicate you want a line break between the link and its description.

**Examples:**

* For all links with descriptions next to them:
 * **`[wp-blogroll showdesc=1]`**

* For all links in category 39 with descriptions **next to** them:
 * **`[wp-blogroll catid=39 showdesc=1]`**

* For all links with descriptions **under them**:
 * **`[wp-blogroll showdesc=1 showbrk=1]`**

= Is there a way to limit the number of links being shown? =

You can limit the number of links shown (per category) by adding **`limit=x`** to the tag where "x" is the number of items you wish to show. 

**Examples:**

* Show 1 link per category:
 * **`[wp-blogroll limit=1]`**

* Show a maximum of 20 links per category:
 * **`[wp-blogroll limit=20]`**

* Show a maximum of 3 links per category ordered by name in ascending order:
 * **`[wp-blogroll limit=2 orderby=name order=ASC]`**


= Is there any way to change the order that the items display within the category? =

Yes.

From version 1.2.3 onward, the plugin supports **`order`** and **`orderby`** parameters.

**`orderby`**

String Value. Defaults is 'name'.

Valid options:

*	`id`
*	`url`
*	`name`
*	`target`
*	`description`
*	`owner` - *User who added bookmark through bookmarks Manager.*
*	`rating`
*	`updated`
*	`rel` - *bookmark relationship (XFN).*
*	`notes`
*	`rss`
*	`length` - *The length of the bookmark name, shortest to longest.*
*	`rand` - *Display bookmarks in random order.*

**`order`**

String value. Default is ASC. Sets ascending or descending order for the orderby parameter.

Valid values:

 *	`ASC`
 *	`DESC`

**Example:**

* For all links ordered by name in descending order:
 * **`[wp-blogroll orderby=name order=DESC]`**

= Is there a way to change the order of the categories as they appear? =

Yes.

From version 1.5.2 onward, the plugin supports the **`catorder`** and **`catorderby`** parameters.

**`catorderby`**

By default the categories are ordered by 'name'.

Valid options:

*	`id`
*	`name` (Default)
*	`slug`
*	`term_group` (untested)

**`catorder`**

String value. Default is ASC. Sets ascending or descending order for the catorderby parameter.

Valid values:

*	`ASC`
*	`DESC`

**Example:**

* Order categories by name descending:
 * **`[wp-blogroll catorderby=name catorder=DESC]`**

* Order categories by number of links ascending:
 * **`[wp-blogroll catorderby=count catorder=ASC]`**


= Is it possible to show the descriptions of the categories under their names? =

Yes.

From version 1.4.0 onward, category descriptions can be shown under the titles using the **`showcatdesc=1`** parameter.

**Example:**

* Show category descriptions under titles:
 * **`[wp-blogroll showcatdesc=1]`**


= Is it possible to exclude categories? =

Yes.

From version 1.5.3 onward, categories can be excluded using the **`excludecat`** parameter.

**Example:**

* Show all category of links but not the category with ID 34:
 * **`[wp-blogroll excludecat=34]`**

* Show all category of links but not the categories with ID 34 and 35:
 * **`[wp-blogroll excludecat=34,35]`**

= Is there a way to force rel="nofollow" attributes to links? =

Yes.

From version 1.5.0 onward, links can be forced to have any **`rel`** you want no matter what they originally were set to.

**Example:**

* Show all links while forcing all links to have **`rel="external"`**:
 * **`[wp-blogroll forcerel=external]`**

* Show all links while forcing all links to have **`rel="nofollow external"`**:
 * **`[wp-blogroll forcerel="nofollow external"]`**

= My links have images, but I also want to show their titles/names =

To do this, version 1.6.0 introduced the `always_show_names=1` and `show_names_under_images=1` parameters.

The `always_show_names=1` parameter makes sure that the names of the links show up even if you're using images.
 
The `show_names_under_images=1` parameter can be used along with `always_show_names=1` to put the link names under the link images rather than next to them.

**Example:**

* Show the names of the links that have images next to their images:
 * **`[wp-blogroll always_show_names=1]`**

* Show the names of the links that have images under their images:
 * **`[wp-blogroll always_show_names=1 show_names_under_images=1]`**

= Some of my links have images, but I'm too lazy to remove them manually. How do I hide the images and just show the link names? =

**Example:**

* Don't show any images even if some of my links have images because I was too lazy to remove them manually:
 * **`[wp-blogroll show_images=0]`**

= How do I make a link to the RSS feed of a url? =

From version 1.7.0 onward, the RSS feeds of websites you're linking to can be shown using the **`show_rss`** parameter. A small icon will appear next to the link indicating an RSS feed that can be subscribed to.

**Example:**

* Render a link to the RSS feeds of sites that have RSS feeds defined:
 * **`[wp-blogroll show_rss=1]`**

= The default RSS icon: How do I change it? =

In combination with the **`show_rss`** parameter, the **`rss_image`** parameter can be used to indicate a custom icon for the RSS links. You must upload the image and link to it using a relative path.

**Example:**

* Render a link to the RSS feeds of sites that have RSS feeds defined with a custom icon:
 * **`[wp-blogroll show_rss=1 rss_image=/wp-includes/images/wlw/wp-icon.png]`**


= Can I make this plugin work with the Live Blogroll plugin? =

Yes. Simply add the livelinks=1 parameter and the Live Blogroll plugin will attempt to get the latest updates from the sites.

**Example:**

* Add Live Blogroll support to links:
 * **`[wp-blogroll livelinks=1]`**

= How can I further customize the output format? =

* Option 1: Open WP-Render-Blogroll.php in your favorite editor an joyfully customize away! Your changes will be lost the next time you update.
* Option 2: Go to the [plugin's page](http://0xtc.com/2009/04/22/wp-render-blogroll-links-plugin.xhtml "WP Render Blogroll Links") and contact the author.

= This plugin has saved me time and headaches. How can I possibly thank you enough? =

* To send donations go to `Settings` -> `WP Blogroll Links`. There's a donate box you can use to thank us as much as you would like :)

== Changelog ==

= 2.1.8 =

* Image URL bugfix.

= 2.1.7 =

* Showdash/Showbrk bugfix.

= 2.1.6 =

* Added showdash option.

= 2.1.5 =

* Minor bugfix for Live Blogroll users.

= 2.1.4 =

* Updated documentation for catname.

= 2.1.3 =

* Officially supporting Wordpress 3.0.1
* Added option for donations.

= 2.1.2 =

* Officially supporting Wordpress 3.x.
* Removed useless postbox bit in the script that was causing javascript errors.

= 2.1.1 =

* Added support for the Live Blogroll plugin.

= 2.1.0 =

* Added linkclass support for setting link classnames.

= 2.0.1 =

* No new functional changes in this version.
* Tested plugin on Wordpress 3.0-RC1

= 2.0.0 =

* Added an admin page with documentation.
* Show/hide of links per click on category
* Bookmarklet for links

= 1.x.x =

* Added limit support
* Added option to show category descriptions
* Added SEO option to force relations. (will render rel="external", rel="nofollow" to links)
* Allowing interoperability with plugins like FAVIROLL
* added support for setting category order
* you can now exclude categories
* You can show the link names even if the links have images.
* You can hide all link images.
* Links that have RSS feeds can optionally show them.
* RSS feed default icon can be changed.