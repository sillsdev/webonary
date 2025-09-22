<?php

/** @var wpdb $wpdb */
/** @var string $webonary_class_path */
/** @var string $webonary_template_path */
/** @var string $webonary_include_path */
global $wpdb, $webonary_class_path, $webonary_template_path, $webonary_include_path;

// User capability. I don't know why this value works in add_management_page. May want to revisit this.
define('SIL_DICTIONARY_USER_CAPABILITY', '10');
define('FONTFOLDER', "/wp-content/uploads/fonts/");
define('SEARCHTABLE', $wpdb->prefix . 'sil_search');
define('REVERSALTABLE', $wpdb->prefix . 'sil_reversals');
if (!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

// The collation for all webonary databases is required to be utf8mb4_general_ci on all versions of MySQL that support it.
define('MYSQL_CHARSET', $wpdb->charset);
define('MYSQL_COLLATION', $wpdb->collate);

define('IS_CLOUD_BACKEND', !empty(get_option('useCloudBackend')));

// This is the Webonary Auto-Loader.
// It must be loaded before the includes below.
function webonary_autoloader($class_name)
{
	global $webonary_class_path, $webonary_include_path;

	$pinyin_namespace = 'Overtrue\\Pinyin\\';
	$pinyin_namespace_length = strlen($pinyin_namespace);

	if (substr($class_name, 0, $pinyin_namespace_length) === $pinyin_namespace) {
		$success = include_once $webonary_include_path . DS . 'pinyin' . DS . 'src' . DS . substr($class_name, $pinyin_namespace_length). '.php';
	}
	else {

		$pos = strpos($class_name, 'Webonary_');

		// class name must begin with "Webonary_"
		if ($pos === false || $pos != 0)
			return null;

		// check for an interface file
		$pos = strpos($class_name, 'Webonary_Interface_');

		if ($pos !== false)
			$class_file = 'interface' . DS . substr($class_name, 19) . '.php';
		else
			$class_file = $class_name . '.php';

		$success = include_once $webonary_class_path . DS . $class_file;
	}

	if ($success === false)
		return new WP_Error('Failed', 'Not able to include ' . $class_name);

	return null;
}

spl_autoload_register('webonary_autoloader');


$root_dir = dirname(dirname(__FILE__));

$webonary_class_path    = $root_dir . DS . 'webonary';
$webonary_template_path = $root_dir . DS . 'templates';
$webonary_include_path  = $root_dir . DS . 'include';

// Configure Webonary Settings
include_once $webonary_include_path . '/configuration.php';
// Code for searching on dictionaries.
include_once $webonary_include_path . '/dictionary-search.php';
// Code for the XHTML importer.
include_once $webonary_include_path . '/xhtml-importer.php';
// A replacement for the search box.
include_once $webonary_include_path . '/searchform_func.php';
// Creates the browse view based on shortcodes
include_once $webonary_include_path . '/browseview_func.php';
// Adds functionality to save the post_name in comment_type and resync comments
include_once $webonary_include_path . '/comments_func.php';
// Widgets
include_once $webonary_include_path . '/widgets.php';
// modify the post content
include_once $webonary_include_path . '/modifycontent.php';

unset($root_dir);
