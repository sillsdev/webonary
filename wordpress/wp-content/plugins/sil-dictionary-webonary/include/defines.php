<?php

/** @var wpdb $wpdb */
global $wpdb;

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
