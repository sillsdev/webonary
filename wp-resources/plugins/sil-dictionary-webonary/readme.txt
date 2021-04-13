=== Plugin Name ===
webonary
Contributors: Steve Miller, Philip Perry, SIL International
Tags: search, dictionary, multilingual, bilingual, ISO 639, language, font
Requires at least: 4.3
Tested up to: 4.4.2
Stable tag: 1.3.7

== Description ==
Webonary gives language groups the ability to publish their bilingual or multilingual dictionaries on the web. 

The SIL Dictionary plugin has several components. It includes a dashboard, an import for XHTML (export from Fieldworks Language Explorer), and multilingual dictionary search.

We don't encourage self-hosting this plugin, but rather recommend you host your dictionary for free at http://www.webonary.org as importing large dictionary files is likely to time-out on your self-hosted website.

Please be aware that you need to use our customized theme: http://www.webonary.org/files/webonary-zeedisplay.zip

== What it does: ==

Each component performs a different task:

= sil_dictionary.php =

Has the hooks for the various components.

= infrastructure.php =

Adds a dashboard in the WordPress Tools menu.

Adds the custom table sil_multilingual_search and the custom taxonomies: languages, part of speech, and semantic domains (which acts somewhat like a thesauras).

Uninstalling the plugin uninstalls the custom table(s) and taxonomies above, as well as the data associated with them.

= search.php =

Gives search capabilities unique to dictionaries, including weighted search results.

= xhmtl-importer.php =

Imports both configured dictionaries and reversal dictionaries. When it does, it sets up data for weighted search results.

== Installation ==

Installation Instructions:

1. Download the plugin and unzip it.
2. Go to the Plugins page in your WordPress Administration area, find the plugin, and click 'Activate'.
3. We highly recommend you use our customized theme "Webonary zeeDisplay" which you can download here: http://www.webonary.org/files/webonary-zeedisplay.zip 

== Table Documentation ==
	
As well as creating the new tables "wp_sil_search" and "wp_sil_reversals", it also misuses some of the columns in wp_posts.

This is how the columns in wp_posts are used:

post_content: the dictionary entry which displays as a wordpress post
post_title: not displayed, but used for some searches
post_name: uses the FLEx GUID, which gets referenced by reversals, etc.
pinged: we use it for keeping track of the import. Final status is "linksconverted". 
menu_order: used for sorting the entries in the browse view (entries need to be sorted correctly before they get exported in FLEx)
post_content_filtered: This is used for storing the letter headword, which is used since July 27th 2017 for selecting which words display under what letter in the browse view

== Known Issues ==

1. The export files from FLEX can be relatively large. The file we're working with now is 6.6 MB, which the default settings of PHP may not handle. At minimum, you may have to change the following settings in php.ini:

	max_execution_time
	
== Frequently Asked Questions ==
For FAQ's please see http://www.webonary.org

== Screenshots ==

== Support ==

Language Software Development, SIL International
http://www.webonary.org