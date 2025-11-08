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
if ( ! defined('ABSPATH') )
	die( '-1' );

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

	$success = include_once($file);
	return $success !== false;
}
spl_autoload_register('webonary_autoloader');

define('WBNY_PLUGIN_URL', plugin_dir_url(__FILE__));

include_once __DIR__ . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'defines.php';

global $wpdb, $search_cookie;

Main::Run();






if (IS_CLOUD_BACKEND) {
	add_filter('posts_pre_query', 'Webonary_Cloud::searchEntries', 10, 2);
	add_filter('comment_post_redirect', 'Webonary_Cloud::commentRedirect');
}

function add_rewrite_rules($aRules): array
{
	//echo "rewrite rules<br>";
	$aNewRules = array('^/([^/]+)/?$' => 'index.php?clean=$matches[1]');
	return $aNewRules + $aRules;
}

add_filter('post_rewrite_rules', 'add_rewrite_rules');

function add_query_vars($query_vars)
{
	if (!in_array('clean', $query_vars))
		$query_vars[] = 'clean';

	if (!in_array('semdomain', $query_vars))
		$query_vars[] = 'semdomain';

	if (!in_array('semnumber', $query_vars))
		$query_vars[] = 'semnumber';

	return $query_vars;
}

add_filter('query_vars', 'add_query_vars');

// register the search widget
function register_custom_widgets(): void
{
	register_widget('Webonary_Search_Widget');
	register_widget('Webonary_Published_Widget');
}

add_action('widgets_init', 'register_custom_widgets');

/**
 * Check for audio file names mangled by the WordPress content editor
 */
add_filter(
	'shortcode_atts_audio',
	function ($out) {

		// the array key we need is either a file extension or "src"
		$audio_types = wp_get_audio_extensions();
		$audio_types[] = 'src';

		// check if this audio short code contains a file name
		foreach ($audio_types as $type) {

			if (empty($out[$type]))
				continue;

			if (!is_string($out[$type]))
				continue;

			// check if the file name contains HTML encoded text
			if (str_contains($out[$type], '&#')) {

				$parts = explode('/', $out[$type]);
				$changed = false;

				foreach ($parts as $key => $part) {

					// decode the text into unicode characters
					if (str_contains($part, '&#')) {
						$parts[$key] = html_entity_decode($part);
						$changed = true;
					}
				}

				// the browser will URL encode the file name if needed
				if ($changed)
					$out[$type] = implode('/', $parts);
			}
		}

		return $out;
	}
);

add_action('network_admin_menu', 'Webonary_Admin::AddLanguageProblemMenuItem');
