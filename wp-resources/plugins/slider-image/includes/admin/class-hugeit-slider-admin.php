<?php

class Hugeit_Slider_Admin {

	private $general_options;

	private $pages;

	private $sliders;

	/**
	 * Hugeit_Slider_Admin constructor.
	 */
	public function __construct() {
		$this->init();
		add_action('admin_menu', array($this, 'admin_menu'));
	}

	/**
	 * @return mixed
	 */
	public function get_pages() {
		return $this->pages;
	}

	public function init() {
		$this->sliders = new Hugeit_Slider_Sliders();
		$this->general_options = new Hugeit_Slider_General_Options();
	}

	public function admin_menu() {
		$this->pages[] = add_menu_page(
			__( 'Huge-IT Slider', 'hugeit-slider' ),
			__( 'Huge-IT Slider', 'hugeit-slider' ),
			'delete_pages',
			'hugeit_slider',
			array( Hugeit_Slider()->admin->sliders, 'load_page' ),
			HUGEIT_SLIDER_ADMIN_IMAGES_URL . '/sidebar.icon.png'
		);

		$this->pages[] = add_submenu_page(
			'hugeit_slider',
			__( 'Sliders', 'hugeit-slider' ),
			__( 'Sliders', 'hugeit-slider' ),
			'delete_pages',
			'hugeit_slider',
			array( Hugeit_Slider()->admin->sliders, 'load_page' )
		);

		$this->pages[] = add_submenu_page(
			'hugeit_slider',
			__( 'Advanced Features PRO', 'hugeit-slider' ),
			__( 'Advanced Features PRO', 'hugeit-slider' ),
			'delete_pages',
			'hugeit_slider_general_options',
			array( Hugeit_Slider()->admin->general_options , 'load_page' )
		);

		$this->pages['licensing'] = add_submenu_page(
			'hugeit_slider',
			__( 'Licensing', 'hugeit-slider' ),
			__( 'Licensing', 'hugeit-slider' ),
			'manage_options',
			'hugeit_slider_licensing',
			array( $this, 'load_licensing_page' )
		);

		$this->pages['featured_plugins'] = add_submenu_page(
			'hugeit_slider',
			__( 'Featured Plugins', 'hugeit-slider' ),
			__( 'Featured Plugins', 'hugeit-slider' ),
			'manage_options',
			'hugeit_slider_featured_plugins',
			array( $this , 'load_featured_plugins_page' )
		);
	}

	public function load_featured_plugins_page() {
		echo Hugeit_Slider_Template_Loader::render(HUGEIT_SLIDER_ADMIN_TEMPLATES_PATH . DIRECTORY_SEPARATOR . 'featured-plugins.php');
	}

	public function load_licensing_page() {
		echo Hugeit_Slider_Template_Loader::render(HUGEIT_SLIDER_ADMIN_TEMPLATES_PATH . DIRECTORY_SEPARATOR . 'licensing.php');
	}
}