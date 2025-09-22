<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class Hugeit_Slider_Shortcode {

	/**
	 * Hugeit_Slider_Shortcode constructor.
	 */
	public function __construct() {
		add_shortcode( 'huge_it_slider', array( $this, 'run_shortcode' ) );
		add_action( 'admin_footer', array( $this, 'inline_popup_content' ) );
		add_action('media_buttons_context', array($this, 'add_editor_media_button'));
	}

	public function run_shortcode($attrs) {
		$attrs = shortcode_atts(array('id' => 'no slider'), $attrs);

		$id = (int)$attrs['id'] === absint($attrs['id']) ? absint($attrs['id']) : false;

		if ( ! $id ) {
			return false;
		}

		do_action('hugeit_slider_before_shortcode', $id);

		return $this->init_frontend($id);
	}

	/**
	 * Add editor media button
	 *
	 * @param $context
	 *
	 * @return string
	 */
	public function add_editor_media_button( $context ) {
		$img = HUGEIT_SLIDER_ADMIN_IMAGES_URL . '/post.button.png';

		$container_id = 'hugeit_slider_media_popup';

		$title = __( 'Select Huge IT Slider to insert into post', 'hugeit-slider' );

		$button_text = __( 'Add Slider', 'hugeit-slider' );

		$context .= '<a class="button thickbox" title="' . $title . '" href="#TB_inline?width=700&height=500&inlineId=' . $container_id . '">
		<span class="wp-media-buttons-icon" style="background: url(' . $img . '); background-repeat: no-repeat; background-position: left bottom;"></span>' . $button_text . '</a>';

		return $context;
	}

	public function inline_popup_content() {
		$sliders = Hugeit_Slider_Slider::get_all_sliders();
		$slider_data = array();

		foreach ( $sliders as $key => $slider ) {
			$id = $slider->get_id();

			$slider_data[$id] = new stdClass();
			$slider_data[$id]->name = $slider->get_name();
		}

		echo Hugeit_Slider_Template_Loader::render(
			HUGEIT_SLIDER_ADMIN_TEMPLATES_PATH . DIRECTORY_SEPARATOR . 'add-slider-popup.php',
			array('sliders' => $slider_data)
		);
	}

	private function init_frontend($id) {
		$slider = new Hugeit_Slider_Slider($id);

		return Hugeit_Slider()->template_loader->load_front_end($slider);
	}
}

new Hugeit_Slider_Shortcode();