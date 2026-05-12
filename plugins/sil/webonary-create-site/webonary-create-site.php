<?php

/**
Plugin Name: Webonary Create Site
Plugin URI: https://github.com/sillsdev/sil-dictionary-webonary
Description: This plugin helps with automating things when creating a new webonary site
Author: SIL Global
Author URI: http://www.sil.org/
Text Domain: webonary-create-site
Version: 0.2
Stable tag: 0.1
License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

use SIL\WebonaryCreateSite\Main;

if (!defined('ABSPATH'))
	die('-1');

function webonary_create_site_autoloader($class_name): bool
{
	// class name must begin with "SIL\WebonaryCreateSite\"
	if (!str_starts_with($class_name, 'SIL\WebonaryCreateSite\\'))
		return false;

	$file = __DIR__ . '/src/' . substr($class_name, 23). '.php';

	$success = include_once(str_replace('\\', '/', $file));
	return $success !== false;
}
spl_autoload_register('webonary_create_site_autoloader');

define('WCS_PLUGIN_URL', plugin_dir_url(__FILE__));

Main::Run();

//include_once 'WebonaryBlogCopier.php';
//global $BlogCopier;
//$BlogCopier = new WebonaryBlogCopier();
















// NB: Removed 22 Aug 2023, Webonary Issue #584
//// overwrites the wp_new_user_notification in includes/pluggable, so that no email with password reset gets sent out
//if (!function_exists('wp_new_user_notification')) {
//	function wp_new_user_notification($user_id, $notify = '')
//	{
//	}
//}






