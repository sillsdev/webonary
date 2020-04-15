<?php

/**
 * Yi
 *
 * Yi: Index Chart, Radical Stroke Index, and other functions specific to the Yi dialect.
 *
 * PHP version 5.2
 *
 * LICENSE GPL v2
 *
 * @package WordPress
 * @since 3.1
 */

/*
Plugin Name: Yi Charts
Plugin URI: http://code.google.com/p/pathway/
Description: Yi: Index Chart, Radical Stroke Index, and other functions specific to the Yi dialect.
Author: SIL International
Author URI: http://www.sil.org/
Text Domain: yi-popup
Domain Path: /lang/
Version: 0.3
Stable tag: 0.2
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

// The class for the Yi lookup charts
require_once( dirname( __FILE__ ) . '/include/yi-popup-charts.php' );

// The .html and .css can't be required or included.

//// Yi Index chart
//require_once( dirname( __FILE__ ) . '/include/idx_chart.html' );
//require_once( dirname( __FILE__ ) . '/include/idx_chart.css' );
//
//// Yi Index chart
//require_once( dirname( __FILE__ ) . '/include/idx_radical.html' );
//require_once( dirname( __FILE__ ) . '/include/idx_radical.css' );

/*
 * Registrations
 */

add_action( 'widgets_init', 'register_yi_widgets' );

// Register the class as a widget
function register_yi_widgets() {
    register_widget( 'yi_popup_charts' );
}

function yi_popup_init() {
	/*
	 * Load the translated strings for the plugin.
	 */
    load_plugin_textdomain('yi-popup', false, dirname(plugin_basename(__FILE__ )).'/lang/');
}
add_action('init', 'yi_popup_init');

?>