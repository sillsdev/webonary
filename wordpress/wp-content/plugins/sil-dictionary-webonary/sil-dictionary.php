<?php

/**
Plugin Name: Webonary
Plugin URI: https://github.com/sillsdev/sil-dictionary-webonary
Description: Webonary gives language groups the ability to publish their bilingual or multilingual dictionaries on the web.
The SIL Dictionary plugin has several components. It includes a dashboard, an import for XHTML (export from Fieldworks Language Explorer), and multilingual dictionary search.
Author: SIL International
Author URI: http://www.sil.org/
Text Domain: sil_dictionary
Domain Path: /lang/
Version: v. 8.3.9
License: MIT
*/

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

// don't load directly
if ( ! defined('ABSPATH') )
	die( '-1' );

include_once __DIR__ . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'defines.php';

/** @var wpdb $wpdb */
global $wpdb;

function webonary_admin_script()
{
	wp_register_script('webonary_admin_script', plugin_dir_url(__FILE__) . 'js/admin_script.js', [], false, true);
	wp_enqueue_script('webonary_admin_script');
	wp_localize_script(
		'webonary_admin_script',
		'webonary_ajax_obj',
		['ajax_url' => admin_url('admin-ajax.php')]
	);

	wp_register_style('webonary_admin_style', plugin_dir_url(__FILE__) . 'css/admin_styles.css', [], false, 'all');
	wp_enqueue_style('webonary_admin_style');
}
add_action('admin_enqueue_scripts', 'webonary_admin_script');


//if(is_admin() ){
	// Menu in the WordPress Dashboard, under tools.
	add_action('admin_menu', 'Webonary_Configuration::add_admin_menu');
	add_action('admin_bar_menu', 'Webonary_Configuration::on_admin_bar', 35);

	// I looked for a register_install_hook, but given the way WordPress plugins
	// can be implemented, I'm not sure it would work right even if I did find one.
	// The register_activation_hook() appears not to work for some reason. But the
	// site won't start up that much any way, and it doesn't hurt anything to call
	// it more than once.
	add_action('init', 'install_sil_dictionary_infrastructure', 0);

	// Take out the custom data when uninstalling the plugin.
	register_uninstall_hook( __FILE__, 'uninstall_sil_dictionary_infrastructure' );
//}


/* Search hook */
add_filter('search_message', 'sil_dictionary_custom_message');

add_filter('posts_request','replace_default_search_filter', 10, 2);

// this executes just before wordpress determines which template page to load
add_action('template_redirect', 'my_enqueue_css');

// add_action('pre_get_posts','no_standard_sort');
add_action('preprocess_comment' , 'preprocess_comment_add_type');

// API for FLEx
add_action('rest_api_init', 'Webonary_API_MyType::Register_New_Routes');

if (get_option('useCloudBackend')) {
	add_filter('posts_pre_query', 'Webonary_Cloud::searchEntries', 10, 2);
}

function add_rewrite_rules($aRules)
{
	//echo "rewrite rules<br>";
	$aNewRules = array('^/([^/]+)/?$' => 'index.php?clean=$matches[1]');
	$aRules = $aNewRules + $aRules;
	return $aRules;
}

add_filter('post_rewrite_rules', 'add_rewrite_rules');

function add_query_vars($qvars)
{
	if (!in_array('clean', $qvars))
		$qvars[] = 'clean';
	return $qvars;
}

add_filter('query_vars', 'add_query_vars');
