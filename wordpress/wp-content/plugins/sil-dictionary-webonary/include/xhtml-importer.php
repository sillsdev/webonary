<?php
/**
 * SIL FieldWorks XHTML Importer
 *
 * Imports data from an SIL FLEX XHTML file. The data may come from SIL
 * FieldWorks or other applications.
 *
 * PHP version 5.2
 *
 * LICENSE GPL v2
 *
 * @package WordPress
 * @subpackage Importer
 * @since 3.1
 */
use Overtrue\Pinyin\Pinyin;
set_time_limit(0);

// don't load directly
if ( ! defined('ABSPATH') )
	die( '-1' );
/*
// Check to make sure we can even load an importer.
if ( ! defined( 'WP_LOAD_IMPORTERS' ) )
    return;
*/
// Include the WordPress Importer.
require_once ABSPATH . 'wp-admin/includes/import.php';

if ( ! class_exists('WP_Importer') )  {
    $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
    if ( file_exists( $class_wp_importer ) )
        include_once $class_wp_importer;
}

// One more check.
if ( ! class_exists( 'WP_Importer' ) )
	return;


//===================================================================================//


/*
 * Register the importer so WordPress knows it exists. Specify the start
 * function as an entry point. Paramaters: $id, $name, $description,
 * $callback.
 */
$pathway_import = new Webonary_Pathway_Xhtml_Import();
register_importer('pathway-xhtml',
		__('SIL FLEX XHTML', 'sil_dictionary'),
		__('Import posts from an SIL FLEX XHTML file.', 'sil_dictionary'),
		array ($pathway_import, 'start'));

//} // class_exists( 'WP_Importer')


//===================================================================================//

function pathway_xhtml_importer_init(){
	/*
	 * Load the translated strings for the plugin.
	 */
    load_plugin_textdomain('sil_dictionary', false, dirname(plugin_basename(__FILE__ )) .'/lang/');
}


//===================================================================================//

/*
 * Hook the importer's init into the WordPress init.
 */
add_action('init', 'pathway_xhtml_importer_init');
