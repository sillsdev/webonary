=== Q and A FAQ and Knowledge Base for WordPress ===
Author: daltonrooney
Author URL: http://madebyraygun.com
Plugin URL: http://wordpress.org/extend/plugins/q-and-a/
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: 1.0.6.2

Create a powerful and easy to use FAQ & knowledge base on your WordPress site.

== Description ==

Create, categorize, and reorder FAQs and insert them into a page with a simple shortcode. Questions are shown/hidden with a simple jQuery animation; users without javascript enabled will click through to the single question page. Uses the native Custom Post Type functionality in WordPress 3.0. and above.

Version 1.0 is <strong>all new</strong> and includes a better FAQ homepage with support for FAQ excerpts, permalinks, better SEO support, nicer search, and more FAQ animation options. 

Need more features? Try <a href="http://madebyraygun.com/wordpress/plugins/q-and-a-plus/">Q & A Plus</a>, our premium version of the plugin. The premium version includes more homepage display options, user ratings and submissions, more FAQ visibility options, and guaranteed support in our [forum](http://madebyraygun.com/support/). 

== Installation ==

Extract the zip file and upload the contents to the wp-content/plugins/ directory of your WordPress installation and then activate the plugin from plugins page. 

Q &amp; A makes it easy to create a full-featured, fully searchable FAQ knowledge base on your WordPress site. You can add an unlimited number of entries, grouped by category, and add a custom search box.

To get started, click on the **Q &amp; A Plus** section of the **Settings** menu of your WordPress Dashboard. The first thing you&#8217;ll want to do is create an FAQ homepage, this is where visitors will be able to view your FAQs. This can be a page that already exists, or the plugin can automatically create the page and add the shortcode for you. By default, the FAQ homepage is &#8220;faqs&#8221;, so if that works for you, go ahead and click the &#8220;Create Page&#8221; button to add a new page to your site.

To use a page that already exists on your site, enter the page slug in the **FAQ homepage** field. For example, the page slug of your "About" page is <code>about</code>. If you'd like your FAQs to be on a sub-page on your site, you can use a slash, so a page called "FAQs" that is a child page of "About" would have the slug <code>about/faqs</code>. You will then need to add the <code>[qa]</code> shortcode to that page.

The default options should work for most sites, so let&#8217;s create a few FAQs and see how they look. From the WordPress Dashboard, look for the **FAQs** menu, and then click **Add New**. Just like a typical WordPress post, you&#8217;ll be able to add a title and body content, as well as set your category. The title is the &#8220;Question&#8221; part of the FAQ and will be displayed on the FAQ page. The content section is hidden by default and will be displayed when the visitor clicks on the title. The category section allows you to organize your FAQs into multiple categories which are displayed on the homepage and on their own individual category pages. A FAQ can belong to multiple categories.

Add your FAQ like you would any normal WordPress post. Once you&#8217;ve added some FAQs, visit your site and take a look. The FAQ homepage will be at yoursite.com/faqs by default, or wherever you set the FAQs homepage slug in the plugin settings.

Take a look at the options on the "Plugin Settings" tab and try them out. You can add a search box on the FAQ homepage, category pages, and control the position of the search box. You can also customimze the animations and other aspects of the FAQ show/hide behavior. Each option has a small question mark next to it. Hover over this mark for a tooltip with more information about that option.

###The [qa] shortcode###

The <code>[qa]</code> shortcode allows you to put your FAQs on any page on your site, and has quite a few options. If you need to create a new FAQ page, just use the shortcode without any options. You can also use the shortcode to place individual FAQs, single FAQ categories, and FAQ pages with custom options anywhere on your site. You can combine most shortcode attributes in any combination you want. Here are the basic Q &amp; A shortcode options:

**FAQ homepage**: <code>[qa]</code> Will insert the entire FAQ homepage anywhere on your site.

**Single category page**: <code>[qa cat=dogs]</code> By specifying a category slug, you can add an entire category of FAQ entries anywhere on your site. You can find the category slug on the **FAQs &rarr; FAQ Categories** page.

**Single FAQ page**: <code>[qa id=123]</code> By specifying an ID, you can insert an individual FAQ entry anywhere on your site.

<h3>Hompage specific shortcodes:</h3>

**Limit**: <code>[qa limit=5]</code> Controls the number of FAQs returned on the FAQ homepage. Use **-1** to return all FAQ entries.

**Enable excerpts**: <code>[qa excerpts=true]</code>. Whether to limit posts length on the homepage. Entries that are longer than 40 words will be shorted and a &#8220;Continue reading&#8221; link will be added. Possible values are **true** or **false**.

<h3>General shortcode attributes:</h3>

**Search**: <code>[qa search=home]</code> Whether to show the search field. Possible values are **home**, **categories**, **both**, or **none** to disable the search field.

**Search position**: <code>[qa searchpos=top]</code> Position of the search box, if enabled. Possible values are **top** or **bottom**.

**Permalinks**: <code>[qa permalinks=true]</code> Whether to show permalinks for individual FAQs. This makes it easier for users to click through and bookmark your content. Possible values are **true** or **false**.

**Animation**: <code>[qa animation=fade]</code> Customize the animation style when opening and closing FAQs. Possible values are **fade**, **slide**, and **none**.

**Accordion**: <code>[qa accordion=true]</code> Clicking on one FAQ entry closes any other open FAQ entries on the page. Setting this to **false** will allow multiple FAQs to be open and visible on the page at the same time.

**Collapsible**: <code>[qa collapsible=true]</code> You can completely disable the show/hide behavior by setting this to **false**.
	
== Frequently Asked Questions ==

= With Javascript disabled, clicking on FAQ titles causes a 404 error.

You may need to refresh your permalinks. From the WP Dashboard, visit "Settings->Permalinks", then click "Save Permalinks".

= I'm having trouble with the plugin. What should I do? =

For the fastest support, use our [forum](http://madebyraygun.com/support/).

== Screenshots ==

1. The FAQ homepage.

2. A single FAQ category page.

3. The plugin settings page.

4. The FAQ entry page.

== Upgrade Notice ==

= 1.0 =

Version 1.0 is an all new plugin. Your FAQs will be be preserved, but you may need to visit the settings page and walk through the plugin setup again. 

== Changelog ==

= 1.0.6 =

* Fixed incorrect site url for search box for sites installed in subdirectories.

* Added text domain for translation.

= 1.0.5 = 

* Changed some javascript functions for better compatibility with older versions of jQuery

* Better formatting for searchbox

* Changed defaults for permalinks, show/hide behavior

* Made "Show category" link optional

* Added in option for breadcrumbs in single FAQ titles

* Cleaned up some CSS


= 1.0.4 =

* Fixed duplicate ID in searchbox

* Fixed missing closing div in single category shortcode

* Changed defaults to show full text instead of excerpts and remove limits on number of returned posts.

* Fixed a couple of validation errors.

= 1.0.3 =

* Disabling post title filter for now.

= 1.0.2 =

* Post titles were being rewritten on category pages.

= 1.0.1 =

* Fixed problem with FAQs that wouldn't open when multiple categories were on the same page.

* Returned missing category headers on single category entries.

1.0 

* All new version of Q & A with a better FAQ homepage and support for FAQ excerpts, permalinks, better SEO support, nicer search, and more FAQ animation options.


