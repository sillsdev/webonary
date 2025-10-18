<?php

/**
 * Class PHPInfo_Page
 *
 * @since 1.0
 *
 * This class handles setting up the page for viewing PHP Info
 */

defined( 'ABSPATH' ) or die();

class PHPInfo_Page {
	public static function init() {
		add_action( 'admin_menu', function() {
			add_options_page(
				__( 'PHP Info', PHPINFO_TD ),
				__( 'PHP Info', PHPINFO_TD ),
				'manage_options',
				PHPINFO_PREFIX . '_phpinfo',
				array( 'PHPInfo_Page', 'display' )
			);
		} );
	}

	public static function display() {
		include_once PHPINFO_DIR . '/includes/templates/page.php';
	}
}