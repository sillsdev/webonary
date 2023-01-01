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

global $wpdb, $search_cookie;

function webonary_admin_script(): void
{
	wp_register_script('webonary_admin_script', plugin_dir_url(__FILE__) . 'js/admin_script.js', [], false, true);
	wp_enqueue_script('webonary_admin_script');
	wp_localize_script(
		'webonary_admin_script',
		'webonary_ajax_obj',
		['ajax_url' => admin_url('admin-ajax.php')]
	);

	wp_register_style('webonary_admin_style', plugin_dir_url(__FILE__) . 'css/admin_styles.css');
	wp_enqueue_style('webonary_admin_style');

	wp_register_script('webonary_toastr_script', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js', [], false, true);
	wp_enqueue_script('webonary_toastr_script');

	wp_register_style('webonary_toastr_style', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css');
	wp_enqueue_style('webonary_toastr_style');
}
add_action('admin_enqueue_scripts', 'webonary_admin_script');

add_action('init', 'Webonary_Utility::LoadTextDomains');

// see: https://www.monsterinsights.com/docs/how-to-disable-the-monsterinsights-dashboard-widget/
// Had to put this here rather than in the theme because the MonsterInsights plugin is
// already loaded before the theme loads.
add_filter('monsterinsights_show_dashboard_widget', '__return_false');

//if(is_admin() ){
	// Menu in the WordPress Dashboard, under tools.
	add_action('admin_menu', 'Webonary_Configuration::add_admin_menu');
	add_action('admin_bar_menu', 'Webonary_Configuration::on_admin_bar', 35);

	// I looked for a register_install_hook, but given the way WordPress plugins
	// can be implemented, I'm not sure it would work right even if I did find one.
	// The register_activation_hook() appears not to work for some reason. But the
	// site won't start up that much any way, and it doesn't hurt anything to call
	// it more than once.
	add_action('init', 'Webonary_Infrastructure::InstallInfrastructure', 0);
//}


/* Search hook */

/* post query hooks */
add_filter('posts_request','replace_default_search_filter', 10, 2);

// be sure these style sheets are loaded last, after the theme
add_action('wp_enqueue_scripts', 'Webonary_Utility::EnqueueJsAndCss', 999991);

// this executes just before wordpress determines which template page to load
add_action('after_setup_theme', 'Webonary_SearchCookie::GetSearchCookie');

// add_action('pre_get_posts','no_standard_sort');
add_action('preprocess_comment' , 'preprocess_comment_add_type');

// API for FLEx
add_action('rest_api_init', 'Webonary_API_MyType::Register_New_Routes');

// API for Webonary Cloud API
add_action('rest_api_init', 'Webonary_Cloud::registerApiRoutes');

// Block all API requests from users not logged in, with exceptions
// See https://developer.wordpress.org/rest-api/frequently-asked-questions/#require-authentication-for-all-requests
add_filter('rest_authentication_errors', function ($result) {

	// If a previous authentication check was applied, pass that result along without modification.
	if (true === $result || is_wp_error($result)) {
		return $result;
	}

	if (is_user_logged_in()) {
		return $result;
	}

	// exceptions, by path
	global $wp;
	$path = add_query_arg(array(), $wp->request);

	if (str_starts_with($path, 'wp-json/wordfence')) {
		return $result;
	}

	if ($path === 'wp-json/webonary/import'
		|| str_starts_with($path, 'wp-json/' . Webonary_Cloud::$apiNamespace)) {
		return $result;
	}

	return new WP_Error(
		'rest_not_logged_in',
		__('This API can only be called if you are logged in first.'),
		array('status' => 401)
	);
});

// NOTE: this was removed because appears to be applying the vernacular settings to the UI language (which it shouldn't)
//// add the correct RTL/LTR class
//function filter_post_class($classes)
//{
//	$rtl = get_option('vernacularRightToLeft') == '1';
//	$align_class = $rtl ? 'right' : 'left';
//
//	if (!in_array($align_class, $classes))
//		$classes[] = $align_class;
//
//	return $classes;
//}
//add_filter('post_class', 'filter_post_class', 10, 3);

if (IS_CLOUD_BACKEND) {
	add_filter('posts_pre_query', 'Webonary_Cloud::searchEntries', 10, 2);
}

function add_rewrite_rules($aRules): array
{
	//echo "rewrite rules<br>";
	$aNewRules = array('^/([^/]+)/?$' => 'index.php?clean=$matches[1]');
	return $aNewRules + $aRules;
}

add_filter('post_rewrite_rules', 'add_rewrite_rules');

function add_query_vars($query_vars)
{
	if (!in_array('clean', $query_vars))
		$query_vars[] = 'clean';
	return $query_vars;
}

add_filter('query_vars', 'add_query_vars');

// register the search widget
function register_custom_widgets(): void
{
	register_widget('Webonary_Search_Widget');
}

add_action('widgets_init', 'register_custom_widgets');
