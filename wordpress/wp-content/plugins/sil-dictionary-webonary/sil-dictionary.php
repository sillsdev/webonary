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

if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

include_once __DIR__ . DS . 'include' . DS . 'defines.php';

// don't load directly
if ( ! defined('ABSPATH') )
	die( '-1' );

/** @var wpdb $wpdb */
global $wpdb, $webonary_class_path, $webonary_template_path, $webonary_include_path;

$this_dir = dirname(__FILE__);

/** @var string $webonary_class_path */
$webonary_class_path = $this_dir . DS . 'webonary';

/** @var string $webonary_template_path */
$webonary_template_path = $this_dir . DS . 'templates';

$webonary_include_path = $this_dir . DS . 'include';

//region Dependencies
function webonary_autoloader($class_name)
{
	global $webonary_class_path, $webonary_include_path;

	if ($class_name == 'Pinyin' ||
		$class_name == 'MemoryFileDictLoader' ||
		$class_name == 'GeneratorFileDictLoader' ||
		$class_name == 'FileDictLoader' ||
		$class_name == 'DictLoaderInterface') {

		$success = include_once $webonary_include_path . DS . 'pinyin' . DS . 'src' . DS . $class_name . '.php';
	}
	else {

		$pos = strpos($class_name, 'Webonary_');

		// class name must begin with "Webonary_"
		if ($pos === false || $pos != 0)
			return null;

		// check for an interface file
		$pos = strpos($class_name, 'Webonary_Interface_');

		if ($pos !== false)
			$class_file = 'interface' . DS . substr($class_name, 19) . '.php';
		else
			$class_file = $class_name . '.php';

		$success = include_once $webonary_class_path . DS . $class_file;
	}

	if ($success === false)
		return new WP_Error('Failed', 'Not able to include ' . $class_name);

	return null;
}

/** @noinspection PhpUnhandledExceptionInspection */
spl_autoload_register('webonary_autoloader');

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



// Infrastructure management: add and remove custom table(s) and custom taxonomies.
include_once $this_dir . '/include/infrastructure.php';
// Configure Webonary Settings
include_once $this_dir . '/include/configuration.php';

// Code for searching on dictionaries.
include_once $this_dir . '/include/dictionary-search.php';
// Code for the XHMTL importer.
include_once $this_dir . '/include/xhtml-importer.php';
// A replacement for the search box.
include_once $this_dir . '/include/searchform_func.php';
// Creates the browse view based on shortcodes
include_once $this_dir . '/include/browseview_func.php';
// Adds functionality to save the post_name in comment_type and resync comments
include_once $this_dir . '/include/comments_func.php';
// API for FLEx
include_once $this_dir . '/include/api.php';
// Widgets
include_once $this_dir . '/include/widgets.php';
// modify the post content
include_once $this_dir . '/include/modifycontent.php';
//endregion

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


//add_action('pre_get_posts','no_standard_sort');
add_action('preprocess_comment' , 'preprocess_comment_add_type');

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
