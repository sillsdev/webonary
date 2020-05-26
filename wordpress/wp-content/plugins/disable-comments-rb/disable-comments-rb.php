<?php
/*
Plugin Name: Disable Comments RB
Plugin URI: https://robosoft.co/wordpress-plugins/disable-comments
Description: Easy tool to disable comments for your blog posts, pages. Admin can disable comments in just few clicks. Delete comments from blog post. 
Version: 1.0.9
Author: rbPlugins
Author URI: https://robosoft.co/wordpress-plugins/disable-comments
License: GPL2
Text Domain: disable-comments-rb
Domain Path: /languages/
*/

if( !defined('WPINC') || !defined("ABSPATH") ) die();

define("RB_DISABLE_COMMENTS_PATH", plugin_dir_path( __FILE__ ) );
define("RB_DISABLE_COMMENTS_VERSION", '1.0.9' );

include_once( RB_DISABLE_COMMENTS_PATH .'class_rb_disable-comments.php');

Rbs_Disable_Comments::get_instance();
