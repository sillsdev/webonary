<?php
/*
Plugin Name: Q and A FAQ and Knowledge Base for WordPress
Plugin URI: http://wordpress.org/extend/plugins/q-and-a/
Description: FAQ plugin for WordPress
Author: Raygun
Version: 1.0.6.2
Text Domain: qa-free
Author URI: http://madebyraygun.com

Copyright 2012 Raygun Design LLC (email : contact@madebyraygun.com)
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

define('Q_A_PLUS_PATH', plugin_dir_path(__FILE__));

define('Q_A_PLUS_LOCATION', plugin_basename(__FILE__));

define('Q_A_PLUS_VERSION', '1.0.6.2');

define('Q_A_PLUS_URL', plugins_url('', __FILE__));


/**
 *  load plugin text domain for translation
 * @since Q and A  1.0.1
 */
function q_and_a_lang_init(): void
{
	load_plugin_textdomain('qa-free', false, basename(dirname(__FILE__)) . '/lang');

	//our main functions file
	include_once(Q_A_PLUS_PATH . 'inc/functions.php');

	// Get the admin page if necessary
	if (is_admin())
		include_once(Q_A_PLUS_PATH . 'admin/q-a-plus-admin.php');
}

add_action('init', 'q_and_a_lang_init');
