<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Sil_Dictionary_Webonary
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/sil-dictionary.php';

	global $wpdb;

	remove_entries();

	$sql = "ALTER DATABASE " . $wpdb->dbname .
	       " CHARACTER SET utf8mb4 " .
	       " COLLATE utf8mb4_general_ci";

	$wpdb->query($sql);

	create_custom_relevance();
	create_search_tables();
	create_reversal_tables();
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
