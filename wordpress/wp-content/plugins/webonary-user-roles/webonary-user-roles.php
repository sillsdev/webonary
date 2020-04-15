<?php

/**
 * Webonary User Roles
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
Plugin Name: Webonary User Roles
Plugin URI: http://www.webonary.org
Description: This changes the permissions for author and possibly other roles for Webonary usage
Author: SIL International
Author URI: http://www.sil.org/
Text Domain: webonary-user-roles
Version: 0.2
Stable tag: 0.1
License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

/* @todo Change the above Plugin URI */
/* @todo Change the licensing above and below. If GPL2, see WP plugin doc about license. */

// don't load directly
if ( ! defined('ABSPATH') )
	die( '-1' );

function add_capability() {
// gets the author role
$role = get_role( 'author' );

// This only works, because it accesses the class instance.
$role->add_cap( 'edit_pages' );
$role->add_cap( 'edit_published_pages' );
$role->add_cap( 'edit_others_pages' );
$role->add_cap( 'publish_pages' );
$role->add_cap( 'delete_published_pages' );
$role->add_cap( 'delete_pages' );

$role->add_cap ('edit_posts');
$role->add_cap ('edit_published_posts');
$role->add_cap ('edit_others_posts');
$role->remove_cap('moderate_comments');
remove_menu_page( 'edit.php' );

}
add_action( 'admin_init', 'add_capability');
?>