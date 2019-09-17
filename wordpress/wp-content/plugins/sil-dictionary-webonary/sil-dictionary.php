<?php

/*
Plugin Name: Webonary
Plugin URI: https://www.webonary.org
Description: Webonary gives language groups the ability to publish their bilingual or multilingual dictionaries on the web.
The SIL Dictionary plugin has several components. It includes a dashboard, an import for XHTML (export from Fieldworks Language Explorer), and multilingual dictionary search.
Author: SIL International
Author URI: http://www.sil.org/
Text Domain: sil_dictionary
Domain Path: /lang/
Version: v. 8.3.0
License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

/* @todo Change the above Plugin URI */
/* @todo Change the licensing above and below. If GPL2, see WP plugin doc about license. */

/**
 * SIL Dictionary
 *
 * SIL Dictionaries: Includes a dashboard, an import for XHTML, and multilingual dictionary search.
 *
 * PHP version 5.2
 *
 * LICENSE GPL v2
 *
 * @package WordPress
 * @since 3.1
 */

include_once __DIR__ . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'defines.php';

// don't load directly
if ( ! defined('ABSPATH') )
	die( '-1' );

/** @var wpdb $wpdb */
global $wpdb;

/*
 * Dependencies
 */
 //To update code from Github through Wordpress Dashboard
//require_once( dirname( __FILE__ ) . '/updater.php');
require_once( dirname( __FILE__ ) . '/include/class_info.php' );
require_once( dirname( __FILE__ ) . '/include/class_utilities.php' );
// Infrastructure management: add and remove custom table(s) and custom taxonomies.
require_once( dirname( __FILE__ ) . '/include/infrastructure.php' );
// Configure Webonary Settings
require_once( dirname( __FILE__ ) . '/include/configuration.php' );
//setting and getting font information
require_once( dirname( __FILE__ ) . '/include/fonts.php' );
// Code for searching on dictionaries.
require_once( dirname( __FILE__ ) . '/include/dictionary-search.php' );
// Code for the XHMTL importer.
require_once( dirname( __FILE__ ) . '/include/xhtml-importer.php' );
// A replacement for the search box.
require_once( dirname( __FILE__ ) . '/include/searchform_func.php' );
// Creates the browse view based on shortcodes
require_once( dirname( __FILE__ ) . '/include/browseview_func.php' );
// Adds functionality to save the post_name in comment_type and resync comments
require_once( dirname( __FILE__ ) . '/include/comments_func.php' );
// API for FLEx
require_once( dirname( __FILE__ ) . '/include/api.php' );
// Widgets
require_once( dirname( __FILE__ ) . '/include/widgets.php' );
//modify the post content
require_once( dirname( __FILE__ ) . '/include/modifycontent.php' );

//if(is_admin() ){
	// Menu in the WordPress Dashboard, under tools.
	add_action( 'admin_menu', 'add_admin_menu' );
	add_action('admin_bar_menu', 'on_admin_bar', 35);

	// I looked for a register_install_hook, but given the way WordPress plugins
	// can be implemented, I'm not sure it would work right even if I did find one.
	// The register_activation_hook() appears not to work for some reason. But the
	// site won't start up that much any way, and it doesn't hurt anything to call
	// it more than once.
	add_action( 'init', 'install_sil_dictionary_infrastructure', 0 );

	// Take out the custom data when uninstalling the plugin.
	register_uninstall_hook( __FILE__, 'uninstall_sil_dictionary_infrastructure' );
//}


/*
 * Search hook
 */
add_filter('search_message', 'sil_dictionary_custom_message');

add_filter('posts_request','replace_default_search_filter');

// this executes just before wordpress determines which template page to load
add_action( 'template_redirect', 'my_enqueue_css' );


//add_action('pre_get_posts','no_standard_sort');
add_action( 'preprocess_comment' , 'preprocess_comment_add_type' );

function add_rewrite_rules($aRules) {
	//echo "rewrite rules<br>";
	$aNewRules = array('^/([^/]+)/?$' => 'index.php?clean=$matches[1]');
	$aRules = $aNewRules + $aRules;
	return $aRules;
}

add_filter('post_rewrite_rules', 'add_rewrite_rules');

function add_query_vars($qvars) {
	$qvars[] = "clean";
	return $qvars;
}

add_filter('query_vars', 'add_query_vars');
