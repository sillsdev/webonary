<?php

/**
 * Special Character Buttons
 *
 * see readme.txt for further information
 *
 * PHP version 5.2
 *
 * LICENSE GPL v2
 *
 * @package WordPress
 * @since 3.1
 */

/*
Plugin Name: Special Character Buttons
Plugin URI: http://pathway.sil.org/webonary/
Description: Provides buttons for special characters
Author: SIL Global
Author URI: http://www.sil.org/
Text Domain: special-characters
Version: 0.1
Stable tag: 0.1
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

// The class for the special-characters
require_once( dirname( __FILE__ ) . '/include/characters-functions.php' );


/*
 * Registrations
 */

add_action( 'widgets_init', 'register_special_characters_widgets' );

// Register the class as a widget
function register_special_characters_widgets() {
    register_widget( 'special_characters' );
}
