<?php
/**
 * Plugin Name: WordPress phpinfo()
 * Plugin URI: http://whoischris.com
 * Description:  This simple plugin adds an option to an administrator's Tools menu which displays standard phpinfo() feedback details to the user and allows to send in email.
 * Author: Chris Flannagan
 * Version: 16.3
 * Author URI: https://whoischris.com/
 *
 * WordPress phpinfo() core file
 *
 * This file contains all the logic required for the plugin
 *
 * @link        https://whoischris.com/
 * @author      Chris Flannagan <me@whoischris.com>
 *
 * @package    WordPress phpinfo()
 * @copyright    Copyright (c) 2017, Chris Flannagan
 * @license        http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, v2 (or newer)
 *
 * @since        WordPress phpinfo() 1.0
 */

defined( 'ABSPATH' ) or die();

/** Setup some constants we will need throughout the plugin */

define( 'PHPINFO_DIR', dirname( __FILE__ ) ); // Path to our plugin directory
define( 'PHPINFO_URL', plugin_dir_url( __FILE__ ) ); // URL to our plugin directory
/**
 * Versioning is a bit weird.  I inherited this it was version 14 or 15.  I completely rewrote it from scratch
 * but for repo purposes I need to keep the versioning numbers he used.  But for my code base it started at 1.0
 */
define( 'PHPINFO_VER', '1.1' );
define( 'PHPINFO_PREFIX', 'cfpi' ); // A nice prefix to help keep our meta keys, cpt slugs, etc. unique
define( 'PHPINFO_TD', PHPINFO_PREFIX . '-domain' ); // Our text domain

require_once PHPINFO_DIR . '/includes/helper-functions.php';

/**
 * Simple class loader
 *
 * All classes loaded other than core for init will not be static for compatibility with unit testing.
 *
 * No PSR-4 for php 5.2 compatibility
 */
phpinfo_custom_autoloader( '/includes/classes' );

if ( class_exists( 'PHPInfo_Core' ) ) {
	PHPInfo_Core::init();
}