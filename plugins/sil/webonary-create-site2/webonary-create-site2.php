<?php

/**
 * Plugin Name: Webonary Create Site 2
 * Plugin URI: http://www.webonary.org
 * Description: This plugin helps with automating things when creating a new webonary site
 * Version: 2.0
 * Author: SIL Global
 * Author URI: http://www.sil.org/
 * License: MIT
 * Text Domain: webonary-create-site2
 * Domain Path: /l10n/lang
 */

use SIL\WebonaryCreateSite2\Main;

if (!defined('ABSPATH'))
	die('-1');

function webonary_create_site2_autoloader($class_name): bool
{
	// class name must begin with "SIL\WebonaryCreateSite2\"
	if (!str_starts_with($class_name, 'SIL\WebonaryCreateSite2\\'))
		return false;

	$file = __DIR__ . '/src/' . substr($class_name, 24). '.php';

	$success = include_once(str_replace('\\', '/', $file));
	return $success !== false;
}
spl_autoload_register('webonary_create_site2_autoloader');

define('WCS2_PLUGIN_URL', plugin_dir_url(__FILE__));
$x = get_plugin_data(__FILE__, false, false);

Main::Run();
