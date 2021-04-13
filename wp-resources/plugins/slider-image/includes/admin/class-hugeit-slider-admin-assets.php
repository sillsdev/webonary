<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Hugeit_Slider_Admin_Assets {

	/**
	 * Hugeit_Slider_Admin_Assets constructor.
	 */
	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'enqueue'));
	}

	public function enqueue($hook) {
		if (in_array($hook, Hugeit_Slider()->admin->get_pages())) {
			$this->enqueue_scripts();
			$this->enqueue_styles($hook);
			$this->localize_script();
            wp_enqueue_style('hugeit_slider_tracking', HUGEIT_SLIDER_STYLESHEETS_URL . '/admin.tracking.css');
		}
		
		if ('post.php' === $hook || 'post-new.php' === $hook) {
			$this->enqueue_add_slider_popup_scripts();
		}

		if('plugins.php' === $hook){
		    $this->enqueue_tracking();
            wp_enqueue_style('hugeit_slider_tracking', HUGEIT_SLIDER_STYLESHEETS_URL . '/admin.tracking.css');
        }
	}

	private function enqueue_scripts() {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-draggable');
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('thickbox');
		wp_enqueue_script('hugeit_slider_free_banner_scripts', HUGEIT_SLIDER_SCRIPTS_URL . '/free-banner.js', array('jquery'));
		wp_enqueue_script('hugeit_slider_admin_scripts', HUGEIT_SLIDER_SCRIPTS_URL . '/admin.js', array('jquery', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-sortable', 'thickbox'));
		wp_enqueue_script('hugeit_slider_simple_slider', HUGEIT_SLIDER_SCRIPTS_URL . '/simple-slider.js');
		wp_enqueue_script('hugeit_slider_js_color', HUGEIT_SLIDER_PLUGIN_URL . '/assets/libs/jscolor/jscolor.js');
		wp_enqueue_media();
	}

	private function enqueue_styles($hook) {
		$pages = Hugeit_Slider()->admin->get_pages();

		if ($pages['featured_plugins'] === $hook) {
			wp_enqueue_style('hugeit_slider_featured_plugins_style', HUGEIT_SLIDER_STYLESHEETS_URL . '/featured-plugins.css');
		} elseif ($pages['licensing'] === $hook) {
			wp_enqueue_style('hugeit_slider_licensing_style', HUGEIT_SLIDER_STYLESHEETS_URL . '/licensing.css');
		}

		wp_enqueue_style('hugeit_slider_free_banner', HUGEIT_SLIDER_STYLESHEETS_URL . '/free-banner.css');
		wp_enqueue_style('hugeit_slider_admin_style', HUGEIT_SLIDER_STYLESHEETS_URL . '/admin.style.css');
		wp_enqueue_style('hugeit_slider_admin_font_style', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
		wp_enqueue_style('hugeit_slider_simple_slider', HUGEIT_SLIDER_STYLESHEETS_URL . '/simple-slider.css');
		wp_enqueue_style('hugeit_slider_shortcode_popup_style', HUGEIT_SLIDER_STYLESHEETS_URL . '/shortcode-popup.css');
	}

	private function enqueue_add_slider_popup_scripts() {
		wp_enqueue_script('hugeit_slider_add_popup', HUGEIT_SLIDER_SCRIPTS_URL . '/post-popup.js', array('jquery', 'thickbox'));
	}

	private function localize_script() {
		wp_localize_script('hugeit_slider_admin_scripts', 'hugeitSliderObject', $this->get_localize_array());
	}

    private function enqueue_tracking()
    {
        wp_enqueue_style('hugeit_slider_tracking', HUGEIT_SLIDER_STYLESHEETS_URL . '/admin.tracking.css');

        if (!Hugeit_Slider()->tracking->is_opted_in()) {
            return false;
        }

        wp_enqueue_script('hugeit_modal', HUGEIT_SLIDER_SCRIPTS_URL . '/hugeit-modal.js', array('jquery'));
        wp_enqueue_script('hugeit_deactivation_feedback', HUGEIT_SLIDER_SCRIPTS_URL . '/deactivation-feedback.js', array('jquery','hugeit_modal'));
        wp_localize_script('hugeit_deactivation_feedback', 'hugeitSliderL10n',array(
            'slug' => Hugeit_Slider()->get_slug()
         ));
        wp_enqueue_style('hugeit_modal', HUGEIT_SLIDER_STYLESHEETS_URL . '/hugeit-modal.css');
	}

	private function get_localize_array() {
		return array(
			'addImageSliderPopupTitle' => __('Choose An Image To Add', 'hugeit-slider'),
			'insertImageButtonText' => __('Add Image', 'hugeit-slider'),
			'removeSlideConfirm' => __('Are you sure you want to remove this slide ?', 'hugeit-slider'),
			'removeSliderConfirm' => __('Are you sure you want to remove this slider ?', 'hugeit-slider'),
			'sliderSuccessfullySaved' => __('Slider Updated', 'hugeit-slider'),
			'sliderSaveFail' => __('OOPS something went wrong.'),
			'itemDeleted' => __('Item Deleted', 'hugeit-slider'),
		);
	}
}

new Hugeit_Slider_Admin_Assets();