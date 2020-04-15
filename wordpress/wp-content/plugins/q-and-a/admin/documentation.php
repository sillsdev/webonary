<h3><?php _e('Table of Contents', 'qa-free'); ?></h3>

<ul style="margin-bottom:20px">
	<li><a href="#general"><?php _e('Introduction', 'qa-free'); ?></a></li>
	<li><a href="#shortcode"><?php _e('The [qa] shortcode', 'qa-free'); ?></a></li>
</ul>


<h3 id="general"><?php _e('Introduction', 'qa-free'); ?></h3>	

<p><?php _e('Q &amp; A makes it easy to create a full-featured, fully searchable FAQ knowledgebase on your WordPress site. You can add an unlimited number of entries, grouped by category.', 'qa-free'); ?></p>

<p><?php _e('To get started, click on the <strong>Q &amp; A Plus</strong> section of the <strong>Settings</strong> menu of your WordPress Dashboard. The first thing you&#8217;ll want to do is create an FAQ homepage, this is where visitors will be able to view your FAQs. This can be a page that already exists, or the plugin can automatically create the page and add the shortcode for you. By default, the FAQ homepage is &#8220;faqs&#8221;, so if that works for you, go ahead and click the &#8220;Create Page&#8221; button to add a new page to your site.', 'qa-free'); ?></p>

<p><?php _e('To use a page that already exists on your site, enter the page slug in the <strong>FAQ homepage</strong> field. For example, the page slug of your "About" page is <code>about</code>. If you\'d like your FAQs to be on a sub-page on your site, you can use a slash, so a page called "FAQs" that is a child page of "About" would have the slug <code>about/faqs</code>. You will then need to add the <code>[qa]</code> shortcode to that page.', 'qa-free'); ?></p>

<p><?php _e('The default options should work for most sites, so let&#8217;s create a few FAQs and see how they look. From the WordPress Dashboard, look for the <strong>FAQs</strong> menu, and then click <strong>Add New</strong>. Just like a typical WordPress post, you&#8217;ll be able to add a title and body content, as well as set your category. The title is the &#8220;Question&#8221; part of the FAQ and will be displayed on the FAQ page. The content section is hidden by default and will be displayed when the visitor clicks on the title. The category section allows you to organize your FAQs into multiple categories which are displayed on the homepage and on their own individual category pages. A FAQ can belong to multiple categories.', 'qa-free'); ?></p>

<p><?php _e('Add your FAQ like you would any normal WordPress post. Once you&#8217;ve added some FAQs, visit your site and take a look. The FAQ homepage will be at yoursite.com/faqs by default, or wherever you set the FAQs homepage slug in the plugin settings.', 'qa-free'); ?></p>

<p><?php _e('Take a look at the options on the "Plugin Settings" tab and try them out. You can add a search box on the FAQ homepage, category pages, and control the position of the search box. You can also customimze the animations and other aspects of the FAQ show/hide behavior. Each option has a small question mark like this next to it. <span class="vtip" title="This is a contextual tooltip. Hover over these to find out what a particular setting does.">?</span>Hover over this mark for a tooltip with more information about that option.', 'qa-free'); ?></p>

<h3 id="shortcode"><?php _e('The [qa] shortcode', 'qa-free'); ?></h3>	

<p><?php _e('The <code>[qa]</code> shortcode allows you to put your FAQs on any page on your site, and has quite a few options. If you need to create a new FAQ page, just use the shortcode without any options. You can also use the shortcode to place individual FAQs, single FAQ categories, and FAQ pages with custom options anywhere on your site. You can combine most shortcode attributes in any combination you want. Here are the basic Q &amp; A shortcode options:', 'qa-free'); ?></p>

<p><?php _e('<strong>FAQ homepage</strong>: <code>[qa]</code> Will insert the entire FAQ homepage anywhere on your site.', 'qa-free'); ?></p>

<p><?php _e('<strong>Single category page</strong>: <code>[qa cat=dogs]</code> By specifying a category slug, you can add an entire category of FAQ entries anywhere on your site. You can find the category slug on the <strong>FAQs &rarr; FAQ Categories</strong> page.','qa-free'); ?></p>

<p><?php _e('<strong>Single FAQ page</strong>: <code>[qa id=123]</code> By specifying an ID, you can insert an individual FAQ entry anywhere on your site.', 'qa-free'); ?></p>

<h3><?php _e('Hompage specific shortcodes:', 'qa-free'); ?></h3>

<p><?php _e('<strong>Limit</strong>: <code>[qa limit=5]</code> Controls the number of FAQs returned on the FAQ homepage. Use <strong>-1</strong> to return all FAQ entries.', 'qa-free'); ?></p>

<p><?php _e('<strong>Enable excerpts</strong>: <code>[qa excerpts=true]</code>. Whether to limit posts length on the homepage. Entries that are longer than 40 words will be shorted and a &#8220;Continue reading&#8221; link will be added. Possible values are <strong>true</strong> or <strong>false</strong>.', 'qa-free'); ?></p>

<h3><?php _e('General shortcode attributes:', 'qa-free'); ?></h3>

<p><?php _e('<strong>Search</strong>: <code>[qa search=home]</code> Whether to show the search field. Possible values are <strong>home</strong>, <strong>categories</strong>, <strong>both</strong>, or <strong>none</strong> to disable the search field.', 'qa-free'); ?></p>

<p><?php _e('<strong>Search position</strong>: <code>[qa searchpos=top]</code> Position of the search box, if enabled. Possible values are <strong>top</strong> or <strong>bottom</strong>.', 'qa-free'); ?></p>

<p><?php _e('<strong>Permalinks</strong>: <code>[qa permalinks=true]</code> Whether to show permalinks for individual FAQs. This makes it easier for users to click through and bookmark your content. Possible values are <strong>true</strong> or <strong>false</strong>.', 'qa-free'); ?></p>

<p><?php _e('<strong>Animation</strong>: <code>[qa animation=fade]</code> Customize the animation style when opening and closing FAQs. Possible values are <strong>fade</strong>, <strong>slide</strong>, and <strong>none</strong>.', 'qa-free'); ?></p>

<p><?php _e('<strong>Accordion</strong>: <code>[qa accordion=true]</code> Clicking on one FAQ entry closes any other open FAQ entries on the page. Setting this to <strong>false</strong> will allow multiple FAQs to be open and visible on the page at the same time.', 'qa-free'); ?></p>

<p><?php _e('<strong>Collapsible</strong>: <code>[qa collapsible=true]</code> You can completely disable the show/hide behavior by setting this to <strong>false</strong>.', 'qa-free'); ?></p>