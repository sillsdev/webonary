<?php

/**
 * Character Chart
 *
 * A popup chart for the special characters above the search box
 *
 * PHP version 5.2
 *
 * LICENSE GPL v2
 *
 * @package WordPress
 * @since 3.1
 */

/*
Plugin Name: Character Chart
Plugin URI: http://www.webonary.org
Description: A popup chart for the special characters above the search box
Author: SIL International
Author URI: http://www.sil.org/
Text Domain: dict
Domain Path: /lang/
Version: 0.4
Stable tag: 0.3
License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

/* @todo Change the above Plugin URI */
/* @todo Change the licensing above and below. If GPL2, see WP plugin doc about license. */

// don't load directly
if ( ! defined('ABSPATH') )
	die( '-1' );

/*
 * Dependencies
 */

// The class for the lookup charts
require_once( dirname( __FILE__ ) . '/include/popup-charts.php' );

/*
 * Registrations
 */

add_action( 'widgets_init', 'register_chart_widgets' );

// Register the class as a widget
function register_chart_widgets() {
    register_widget( 'popup_charts' );
}

function popup_init() {
	/*
	 * Load the translated strings for the plugin.
	 */
    load_plugin_textdomain('popup', false, dirname(plugin_basename(__FILE__ )).'/lang/');
}
add_action('init', 'popup_init');

?>