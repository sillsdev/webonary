<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Hugeit_Slider_General_Options {

	/**
	 * Hugeit_Slider_General_Options constructor.
	 */
	public function __construct() {
		add_action( 'hugeit_slider_save_general_options', array( $this, 'save_options' ) );
	}

	/**
	 * Loads General options page
	 */
	public function load_page() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'hugeit_slider_general_options' ) {
			if ( isset( $_GET['task'] ) ) {
				if ( $_GET['task'] == 'save' && isset($_POST['params'])) {
					do_action( 'hugeit_slider_save_general_options', $_POST['params'] );
				} else {
					$this->render_page();
				}
			} else {
				$this->render_page();
			}
		}
	}

	public function render_page() {
		echo Hugeit_Slider_Template_Loader::render(HUGEIT_SLIDER_ADMIN_TEMPLATES_PATH . '/general-options.php');
	}

	public function save_options($options) {
		foreach ( $options as $name => $value ) {
			if ( method_exists( 'Hugeit_Slider_Options', 'set_' . $name ) ) {
				try {
					call_user_func( array( 'Hugeit_Slider_Options', 'set_' . $name ), $value );
				} catch ( Exception $e ) {
					echo '<div class="updated"><p><strong>' . $e->getMessage() . '</strong></p></div>';
				}
			}
		}

		echo '<div class="updated"><p><strong>' . __('Item Saved') . '</strong></p></div>';
		$this->render_page();
	}
}