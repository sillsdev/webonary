=== Plugin Name ===
webonary
Contributors: Steve Miller, Philip Perry, SIL International
Tags: search, dictionary, multilingual, bilingual, ISO 639, language, font
Requires at least: 4.3
Tested up to: 4.4.2
Stable tag: 1.3.7

~Current Version:5.4.6~

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
	
== Known Issues ==

1. The export files from FLEX can be relatively large. The file we're working with now is 6.6 MB, which the default settings of PHP may not handle. At minimum, you may have to change the following settings in php.ini:

	max_execution_time
	
== Frequently Asked Questions ==
For FAQ's please see http://www.webonary.org

== Screenshots ==

== Support ==

Language Software Development, SIL International
http://www.webonary.org