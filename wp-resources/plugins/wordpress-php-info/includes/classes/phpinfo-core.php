<?php
/**
 * Class PHPInfo_Core
 *
 * @since 1.0
 *
 * Setup our initial plugin needs
 */

defined( 'ABSPATH' ) or die();

class PHPInfo_Core {
	/**
	 * Initiate the plugin
	 */
	public static function init() {
		if ( ! class_exists( 'PHPInfo_Page' ) ) {
			die();
		}
		/**
		 * Register our settings page for viewing phpinfo()
		 */
		PHPInfo_Page::init();

		/**
		 * Email out phpinfo after form submission on settings page
		 */
		add_action( 'admin_post_' . PHPINFO_PREFIX . '_submit_phpinfo_form_action', 'email_phpinfo_form_handler' );
		add_action( 'admin_enqueue_scripts', array( 'PHPInfo_Core', 'admin_scripts' ) );
	}

	public static function admin_scripts() {
		wp_enqueue_script( 'phpinfoscripts', PHPINFO_URL . '/js/phpinfo-js.js' );
	}
}