<?php

/**
Plugin Name: Webonary
Plugin URI: https://github.com/sillsdev/sil-dictionary-webonary
Description: Webonary gives language groups the ability to publish their bilingual or multilingual dictionaries on the web.
The SIL Dictionary plugin has several components. It includes a dashboard, an import for XHTML (export from Fieldworks Language Explorer), and multilingual dictionary search.
Author: SIL Global
Author URI: http://www.sil.org/
Text Domain: sil_dictionary
Domain Path: /include/lang/
Version: v. 8.3.9
License: MIT
*/

use SIL\Webonary\Main;

// don't load directly
if (!defined('ABSPATH'))
	die('-1');

/**
 * This function loads the Webonary classes when needed.
 *
 * @param string $class_name
 * @return bool
 */
function webonary_autoloader(string $class_name): bool
{
	if (str_starts_with($class_name, 'Overtrue\\Pinyin\\'))
		$file = __DIR__ . '/pinyin/src/' . substr($class_name, 16). '.php';
	elseif (str_starts_with($class_name, 'SIL\\Webonary\\'))
		$file = __DIR__ . '/src/' . substr($class_name, 13). '.php';
	elseif (str_starts_with($class_name, 'Webonary_'))
		$file = __DIR__ . '/webonary/' . $class_name . '.php';
	else
		return false;

	$success = include_once(str_replace('\\', '/', $file));
	return $success !== false;
}
spl_autoload_register('webonary_autoloader');

define('WBNY_PLUGIN_URL', plugin_dir_url(__FILE__));

include_once __DIR__ . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'defines.php';

global $wpdb, $search_cookie;

Main::Run();
