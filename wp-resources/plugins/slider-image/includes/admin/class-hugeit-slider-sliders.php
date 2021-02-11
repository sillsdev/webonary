<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Hugeit_Slider_Sliders {

	/**
	 * Hugeit_Slider_Sliders constructor.
	 */
	public function __construct() {
		add_action('hugeit_slider_save_slider', array($this, 'save_slider'), 10, 3);
	}

	public function load_page() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'hugeit_slider' ) {
			if ( isset( $_GET['task'] ) ) {
				switch ( $_GET['task'] ) {
					case 'add' :
						if ( ! isset( $_REQUEST['hugeit_slider_add_slider_nonce'] ) || ! wp_verify_nonce( $_REQUEST['hugeit_slider_add_slider_nonce'], 'add_slider' ) ) {
							wp_die( __( 'Security check failure', 'hugeit-slider' ) . '.' );
						}

						$new_slider = new Hugeit_Slider_Slider();
						$new_slider->save();

						$new_slider_id = $new_slider->get_id();
						$url = wp_nonce_url('admin.php?page=hugeit_slider&task=edit&id=' . $new_slider_id, 'edit_slider_' . $new_slider_id, 'hugeit_slider_edit_slider_nonce');
						$url = htmlspecialchars_decode($url);

						header('Location: ' . $url);
						ob_end_flush();

						break;
					case 'edit' :
						if ( ! isset( $_REQUEST['hugeit_slider_edit_slider_nonce'], $_GET['id'] ) || ! wp_verify_nonce( $_REQUEST['hugeit_slider_edit_slider_nonce'], 'edit_slider_' . $_GET['id'] ) ) {
							wp_die( __( 'Security check failure', 'hugeit-slider' ) . '.' );
						}

						if ( isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {
							$id = absint( $_GET['id'] );

							$this->render_single_slider_page( $id );
						} else {
							$this->render_main_page();
						}
						break;
					default :
						$this->render_main_page();
				}
			} else {
				$this->render_main_page();
			}
		}
	}

	private function render_single_slider_page( $id = NULL ) {
		$slider = NULL === $id ? new Hugeit_Slider_Slider() : new Hugeit_Slider_Slider($id);

		if ($slider->get_id() === NULL) {
			$slider->save();
			$_GET['id'] = $slider->get_id();
		}

		$add_slider_safe_link = wp_nonce_url('admin.php?page=hugeit_slider&task=add', 'add_slider', 'hugeit_slider_add_slider_nonce');
		$save_slider_nonce = wp_create_nonce('save_slider_' . $slider->get_id());
		$all_sliders_id_name_pair = Hugeit_Slider_Slider::get_all_sliders_id_name_pair();

		echo Hugeit_Slider_Template_Loader::render(HUGEIT_SLIDER_ADMIN_TEMPLATES_PATH . '/single-slider.php', array(
			'slider' => $slider,
			'add_slider_safe_link' => $add_slider_safe_link,
			'save_slider_nonce' => $save_slider_nonce,
			'all_sliders_id_name_pair' => $all_sliders_id_name_pair,
		));
	}

	public function render_main_page() {

		global $wpdb;
		$pagination_html  = "";

		$search = '';

		if (isset($_POST['search'])) {
			$search = sanitize_text_field(urldecode($_POST['search']));
		}

		$query = "SELECT id FROM " . Hugeit_Slider()->get_slider_table_name() . (!empty($search) ? " WHERE name LIKE '%" . $search . "%'" : '');
		$total = $wpdb->get_var("SELECT COUNT(1) FROM (${query}) AS combined_table");

		$items_per_page = 30;
		$page = isset( $_GET['cpage'] ) ? absint($_GET['cpage']) : 1;
		$offset = ( $page * $items_per_page ) - $items_per_page;
		$sliders = $wpdb->get_results( $query . " ORDER BY id ASC LIMIT ${offset}, ${items_per_page}" );

		foreach ($sliders as &$slider) {
			$slider = new Hugeit_Slider_Slider($slider->id);
		}

		unset($slider);

		$totalPage = ceil( $total / $items_per_page );

		if ($totalPage > 1) {
			$pagination_html = Hugeit_Slider_Html_Loader::get_pagination_html($page, $totalPage);
		}

		$add_slider_nonce = wp_nonce_url('admin.php?page=hugeit_slider&task=add', 'add_slider', 'hugeit_slider_add_slider_nonce');

		echo Hugeit_Slider_Template_Loader::render(HUGEIT_SLIDER_ADMIN_TEMPLATES_PATH . '/sliders.php', array(
			'pagination_html' => $pagination_html,
			'search' => $search,
			'sliders' => $sliders,
			'add_slider_nonce' => $add_slider_nonce,
		));
	}

	public function save_slider($slider_id, $slider_data, $slides) {
		$slider = new Hugeit_Slider_Slider($slider_id);

		foreach ( $slider_data as $property_name => $property_value ) {
			$function_name = 'set_' . $property_name;

			if (method_exists($slider, $function_name)) {
				try {
					call_user_func(array($slider, $function_name), $property_value);
				} catch (Exception $e) {
					die($e->getMessage());
				}
			} 
		}

		foreach ( $slides as $order => $slide_data ) {
			$slide = Hugeit_Slider_Slide::get_slide($slide_data['id']);

			if (!($slide instanceof Hugeit_Slider_Slide)) {
				wp_die('$slide is not an instance of Hugeit_Slider_Slide');
			}

			$slide->set_order( $order );

			foreach ( $slide_data as $slide_property_name => $slide_property_value ) {
				$function_name = 'set_' . $slide_property_name;

				if (method_exists($slide, $function_name)) {
					try {
						call_user_func(array($slide, $function_name), $slide_property_value);
					} catch (Exception $e) {
						die($e->getMessage());
					}
				}
			}

			$slides[$order] = $slide;
		}

		try {
			$slider->set_slides($slides);
		} catch (Exception $e) {
			die($e->getMessage());
		}

		$GLOBALS['hugeit_slider_save_result'] = $slider->save();
	}
}