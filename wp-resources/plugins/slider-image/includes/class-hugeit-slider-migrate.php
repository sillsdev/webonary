<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Hugeit_Slider_Migrate {
	public static function migrate() {
		global $wpdb;

		$slider_diff = array(
			'id' => array(
				'name' => 'id',
			),
			'name' => array(
				'name' => 'name',
			),
			'sl_height' => array(
				'name' => 'height',
			),
			'sl_width' => array(
				'name' => 'width',
			),
			'itemscount' => array(
				'name' => 'itemscount',
			),
			'pause_on_hover' => array(
				'name' => 'pause_on_hover',
				'replace' => array(
					'on' => 1,
					'off' => 0,
				),
			),
			'slider_list_views_s' => array(
				'name' => 'view',
				'replace' => array(
					'none' => 'none',
					'carousel1' => 'carousel1',
					'thumb_view' => 'thumb_view'
				),
			),
			'slider_list_effects_s' => array(
				'name' => 'effect',
				'replace' => array(
					'none' => 'none',
					'cubeH' => 'cube_h',
					'cubeV' => 'cube_v',
					'fade' => 'fade',
					'sliceH' => 'slice_h',
					'sliceV' => 'slice_v',
					'slideH' => 'slide_h',
					'slideV' => 'slide_v',
					'scaleOut' => 'scale_out',
					'scaleIn' => 'scale_in',
					'blockScale' => 'block_scale',
					'kaleidoscope' => 'kaleidoscope',
					'fan' => 'fan',
					'blindH' => 'blind_h',
					'blindV' => 'blind_v',
					'random' => 'random'
				),
			),
			'description' => array(
				'name' => 'pause_time',
			),
			'param' => array(
				'name' => 'change_speed',
			),
			'sl_position' => array(
				'name' => 'position',
			),
			'sl_loading_icon' => array(
				'name' => 'show_loading_icon',
				'replace' => array(
					'on' => 1,
					'off' => 0,
				),
			),
			'show_thumb' => array(
				'name' => 'navigate_by',
				'replace' => array(
					'dotstop' => 'dot',
					'nonav' => 'none',
					'thumbnails' => 'thumbnail'
				),
			),
			'video_autoplay' => array(
				'name' => 'video_autoplay',
				'replace' => array(
					'on' => 1,
					'off' => 0,
				),
			),
			'random_images' => array(
				'name' => 'random',
				'replace' => array(
					'on' => 1,
					'off' => 0,
				),
			),
		);

		$sliders = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'huge_itslider_sliders', ARRAY_A);

		foreach ( $sliders as $slider ) {

			$old_slider_id = $slider['id'];
			$new_slider = new Hugeit_Slider_Slider();

			foreach ( $slider_diff as $old => $new ) {
				try {
					$function_name = 'set_' . $new['name'];

					if (method_exists($new_slider, $function_name) && isset($new['replace'], $new['replace'][$slider[$old]])) {

						call_user_func(array($new_slider, $function_name), $new['replace'][$slider[$old]]);
					} elseif (method_exists($new_slider, $function_name)) {
						call_user_func(array($new_slider, $function_name), $slider[$old]);
					}
				} catch (Exception $e) {
					$errors[$slider['id']] = $e->getMessage();
				}
			}

			$new_slider_id = $new_slider->save($old_slider_id);

			$new_slider->set_slides(self::migrate_slides($old_slider_id, $new_slider_id['slider_id']));

			$new_slider->save();

			$new_sliders[] = $new_slider;
		}
	}

	private static function migrate_slides($slider_old_id, $slider_new_id) {
		global $wpdb;

		$new_slides = array();

		$slide_diff = array(
			'id' => array(
				'name' => 'id',
			),
			'slider_id' => array(
				'name' => 'slider_id',
			),
			'name' => array(
				'image' => array(
					'name' => 'title',
				),
				'video' => array(
					'name' => 'quality',
				),
				'post' => array(
					'name' => 'term_id',
				),
			),
			'description' => array(
				'image' => array(
					'name' => 'description',
				),
				'video' => array(
					'name' => 'volume',
				),
			),
			'image_url' => array(
				'image' => array(
					'name' => 'attachment_id',
				),
				'video' => array(
					'name' => 'url',
				),
			),
			'sl_url' => array(
				'image' => array(
					'name' => 'url',
				),
				'video' => array(
					'name' => 'show_controls',
					'replace' => array(
						'on' => 1,
						'off' => 0,
					),
				),
				'post' => array(
					'name' => 'max_post_count',
				),
			),
			'sl_type' => array(
				'name' => 'type',
				'replace' => array(
					'last_posts' => 'post',
					'video' => 'video',
				),
			),
			'link_target' => array(
				'image' => array(
					'name' => 'in_new_tab',
					'replace' => array(
						'on' => 1,
						'off' => 0
					)
				),
				'video' => array(
					'name' => 'show_info',
					'replace' => array(
						'on' => 1,
						'off' => 0,
					),
				),
				'post' => array(
					'name' => 'in_new_tab',
					'replace' => array(
						'off' => 0,
						'on' => 1,
					),
				),
			),
			'sl_stitle' => array(
				'post' => array(
					'name' => 'show_title',
					'replace' => array(
						'off' => 0,
						'on' => 1,
						'1' => 1,
					),
				),
			),
			'sl_sdesc' => array(
				'post' => array(
					'name' => 'show_description',
					'replace' => array(
						'off' => 0,
						'on' => 1,
						'1' => 1,
					),
				),
			),
			'sl_postlink' => array(
				'post' => array(
					'name' => 'go_to_post',
				),
			),
			'ordering' => array(
				'name' => 'order',
			),
		);

		$slides = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'huge_itslider_images WHERE slider_id = ' . $slider_old_id, ARRAY_A);

		foreach ( $slides as $slide ) {
			$type = key_exists($slide['sl_type'], $slide_diff['sl_type']['replace']) ? $slide_diff['sl_type']['replace'][$slide['sl_type']] : 'image';
			$new_slide = Hugeit_Slider_Slide::get_slide($type);
			self::correct_slide_data($slide, $slide_diff);

			foreach ( $slide_diff as $old => $new ) {
				$is_common = self::is_common($new);

				try {
					$function_name = 'set_' . ($is_common ? $new['name'] : (isset($new[$type], $new[$type]['name']) ? $new[$type]['name'] : ''));

					if (method_exists($new_slide, $function_name) && ($is_common ? isset($new['replace'], $new['replace'][$slide[$old]]) : isset($new[$type]['replace'], $new[$type]['replace'][$slide[$old]]))) {
						$arg = $function_name !== 'set_slider_id' ? ($is_common ? $new['replace'][$slide[$old]] : $new[$type]['replace'][$slide[$old]]) : $slider_new_id;
						call_user_func(array($new_slide, $function_name), $arg);
					} elseif (method_exists($new_slide, $function_name)) {
						$arg = $function_name !== 'set_slider_id' ? (isset($new[$type]['replace'], $new[$type]['replace'][$slide[$old]]) ? $new[$type]['replace'][$slide[$old]] : $slide[$old]) : $slider_new_id;
						call_user_func(array($new_slide, $function_name), $arg);
					}
				} catch (Exception $e) {
					$errors[$slide['id']] = $e->getMessage();
				}
			}

			$new_slides[] = $new_slide;
		}

		return $new_slides;
	}

	private static function get_attachment_id_by_url( $url ) {
		$parsed_url  = explode( parse_url( WP_CONTENT_URL, PHP_URL_PATH ), $url );
		$this_host = str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
		$file_host = str_ireplace( 'www.', '', parse_url( $url, PHP_URL_HOST ) );

		if ( ! isset( $parsed_url[1] ) || empty( $parsed_url[1] ) || ( $this_host != $file_host ) ) {
			return false;
		}

		global $wpdb;
		$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid RLIKE %s;", $parsed_url[1] ) );

		return isset($attachment[0]) ? $attachment[0] : 0;
	}

	private static function is_common($arr) {
		return !(array_key_exists('image', $arr) || array_key_exists('video', $arr) || array_key_exists('post', $arr));
	}

	private static function correct_slide_data(&$slide, &$replaces = NULL) {
		global $wpdb;

		if ($slide['sl_type'] === 'last_posts') {
			$slide['name'] = $wpdb->get_var('SELECT term_id FROM ' . $wpdb->terms . ' WHERE name = "' . sanitize_text_field($slide['name']) . '" LIMIT 1');//get_terms(array('name' => $slide['name'], 'hide_empty' => false))[0]->term_id;

			if ($slide['sl_sdesc'] !== 'on' && $slide['sl_sdesc'] != '1' && $slide['sl_sdesc'] !== '0') {
				$slide['sl_sdesc'] = 'off';
			}

			if ($slide['sl_stitle'] !== 'on' && $slide['sl_stitle'] != '1' && $slide['sl_stitle'] !== '0') {
				$slide['sl_stitle'] = 'off';
			}

			if ($slide['sl_postlink'] !== 'on' && $slide['sl_postlink'] != '1' && $slide['sl_postlink'] !== '0') {
				$slide['sl_postlink'] = 'off';
			}

			if ($slide['link_target'] !== 'on') {
				$slide['link_target'] = 'off';
			}
		}

		if ($slide['sl_type'] !== 'last_posts' && $slide['sl_type'] !== 'video') {
			$slide['sl_type'] = 'image';

			if ($slide['link_target'] !== 'on') {
				$slide['link_target'] = 'off';
			}
		}

		if ($slide['sl_type'] === 'image') {
			$attachment_id = self::get_attachment_id_by_url($slide['image_url']);

			if (!$attachment_id) {
				$is_standard_slide_1 = false !== strpos($slide['image_url'], '/Front_images/slides/slide1.jpg');
				$is_standard_slide_2 = false !== strpos($slide['image_url'], '/Front_images/slides/slide2.jpg');
				$is_standard_slide_3 = false !== strpos($slide['image_url'], '/Front_images/slides/slide3.jpg');

				if ($is_standard_slide_1 || $is_standard_slide_2 || $is_standard_slide_3) {
					foreach (array($is_standard_slide_1, $is_standard_slide_2, $is_standard_slide_3) as $key => $item) {
						if ($item) {
							$which = $key + 1;

							break;
						}
					}

					switch ($which) {
						case 1 :
							$whichInEnglish = 'First';
							break;
						case 2 :
							$whichInEnglish = 'Second';
							break;
						case 3 :
							$whichInEnglish = 'Third';
							break;
					}

					$wp_upload_dir = wp_upload_dir();

					if (!file_exists($wp_upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'hugeit-slider')) {
						mkdir($wp_upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'hugeit-slider', 0777, true);
					}

					copy(HUGEIT_SLIDER_FRONT_IMAGES_PATH . DIRECTORY_SEPARATOR . 'slides' . DIRECTORY_SEPARATOR . 'slide' . $which . '.jpg', $wp_upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'hugeit-slider' . DIRECTORY_SEPARATOR . 'slide' . $which . '.jpg');
					$attachment_id = wp_insert_attachment(array('post_title' => __('Huge-IT ' . $whichInEnglish . ' Slide.', 'hugeit-slider'), 'post_content' => '', 'post_status' => 'publish', 'post_mime_type' => 'jpg'), $wp_upload_dir['basedir'] . '/hugeit-slider/slide' . $which . '.jpg');
				}
			}

			$slide['image_url'] = $attachment_id;
		}

		if ($slide['sl_type'] === 'video') {
			if ($slide['sl_url'] !== 'on') {
				$slide['sl_url'] = 'off';
			}

			if ($slide['name'] == '280') {
				$slide['name'] = 240;
			}

			$slide['name'] = preg_replace("/[^0-9]/", "", $slide['name']);

			if (Hugeit_Slider_Helpers::youtube_or_vimeo($slide['image_url']) === 'vimeo') {
				$replaces['link_target']['video'] = array(
					'name' => 'control_color',
				);
			} else {
				$replaces['link_target']['video'] = array(
					'name' => 'show_info',
					'replace' => array(
						'on' => 1,
						'off' => 0,
					),
				);

				if ($slide['link_target'] !== 'on') {
					$slide['link_target'] = 'off';
				}
			}
		}
	}

	public static function migrate_options() {
		global $wpdb;

		$options = $wpdb->get_results('SELECT `name`, `title`, `value` FROM ' . $wpdb->prefix . 'huge_itslider_params', ARRAY_A);

		$replaces = array(
			'slider_crop_image' => array(
				'name' => 'crop_image',
				'replace' => array(
					'crop' => 'fill',
					'resize' => 'stretch'
				),
			),
			'slider_title_color' => array(
				'name' => 'title_color',
			),
			'slider_title_font_size' => array(
				'name' => 'title_font_size',
			),
			'slider_description_color' => array(
				'name' => 'description_color',
			),
			'slider_description_font_size' => array(
				'name' => 'description_font_size',
			),
			'slider_title_position' => array(
				'name' => 'title_position',
				'replace' => array(
					'left-top' => 13,
					'center-top' => 23,
					'right-top' => 33,
					'left-middle' => 12,
					'center-middle' => 22,
					'right-middle' => 32,
					'left-bottom' => 11,
					'center-bottom' => 21,
					'right-bottom' => 31,
				),
			),
			'slider_description_position' => array(
				'name' => 'description_position',
				'replace' => array(
					'left-top' => 13,
					'center-top' => 23,
					'right-top' => 33,
					'left-middle' => 12,
					'center-middle' => 22,
					'right-middle' => 32,
					'left-bottom' => 11,
					'center-bottom' => 21,
					'right-bottom' => 31,
				),
			),
			'slider_title_border_size' => array(
				'name' => 'title_border_size',
			),
			'slider_title_border_color' => array(
				'name' => 'title_border_color',
			),
			'slider_title_border_radius' => array(
				'name' => 'title_border_radius',
			),
			'slider_description_border_size' => array(
				'name' => 'description_border_size',
			),
			'slider_description_border_color' => array(
				'name' => 'description_border_color',
			),
			'slider_description_border_radius' => array(
				'name' => 'description_border_radius',
			),
			'slider_slideshow_border_size' => array(
				'name' => 'slideshow_border_size',
			),
			'slider_slideshow_border_color' => array(
				'name' => 'slideshow_border_color',
			),
			'slider_slideshow_border_radius' => array(
				'name' => 'slideshow_border_radius',
			),
			'slider_navigation_type' => array(
				'name' => 'navigation_type',
			),
			'slider_title_background_color' => array(
				'name' => 'title_background_color',
			),
			'slider_description_background_color' => array(
				'name' => 'description_background_color',
			),
			'slider_slider_background_color' => array(
				'name' => 'slider_background_color',
			),
			'slider_active_dot_color' => array(
				'name' => 'active_dot_color',
			),
			'slider_dots_color' => array(
				'name' => 'dots_color',
			),
			'slider_description_width' => array(
				'name' => 'description_width',
			),
			'slider_description_height' => array(
				'name' => 'description_height',
			),
			'slider_description_background_transparency' => array(
				'name' => 'description_background_transparency',
			),
			'slider_description_text_align' => array(
				'name' => 'description_text_align',
			),
			'slider_title_width' => array(
				'name' => 'title_width',
			),
			'slider_title_height' => array(
				'name' => 'title_height',
			),
			'slider_title_background_transparency' => array(
				'name' => 'title_background_transparency',
			),
			'slider_title_text_align' => array(
				'name' => 'title_text_align',
			),
			'slider_title_has_margin' => array(
				'name' => 'title_has_margin',
				'replace' => array(
					'on' => 1,
					'off' => 0,
				),
			),
			'slider_share_buttons' => array(
				'name' => 'share_buttons',
				'replace' => array(
					'on' => 1,
					'off' => 0,
				),
			),
			'slider_share_buttons_facebook' => array(
				'name' => 'share_buttons_facebook',
				'replace' => array(
					'on' => 1,
					'off' => 0,
				),
			),
			'slider_share_buttons_twitter' => array(
				'name' => 'share_buttons_twitter',
				'replace' => array(
					'on' => 1,
					'off' => 0,
				),
			),
			'slider_share_buttons_gp' => array(
				'name' => 'share_buttons_gp',
				'replace' => array(
					'on' => 1,
					'off' => 0,
				),
			),
			'slider_share_buttons_pinterest' => array(
				'name' => 'share_buttons_pinterest',
				'replace' => array(
					'on' => 1,
					'off' => 0,
				),
			),
			'slider_share_buttons_linkedin' => array(
				'name' => 'share_buttons_linkedin',
				'replace' => array(
					'on' => 1,
					'off' => 0,
				),
			),
			'slider_share_buttons_tumblr' => array(
				'name' => 'share_buttons_tumblr',
				'replace' => array(
					'on' => 1,
					'off' => 0,
				),
			),
			'slider_share_buttons_style' => array(
				'name' => 'share_buttons_style',
			),
			'slider_share_buttons_hover_style' => array(
				'name' => 'share_buttons_hover_style',
			),
			'slider_description_has_margin' => array(
				'name' => 'description_has_margin',
				'replace' => array(
					'on' => 1,
					'off' => 0,
				),
			),
			'slider_show_arrows' => array(
				'name' => 'show_arrows',
				'replace' => array(
					'on' => 1,
					'off' => 0,
				),
			),
			'loading_icon_type' => array(
				'name' => 'loading_icon_type',
			),
			'slider_thumb_count_slides' => array(
				'name' => 'thumb_count_slides',
			),
			'slider_dots_position_new' => array(
				'name' => 'navigation_position',
				'replace' => array(
					'dotstop' => 'top',
					'dotsbottom' => 'bottom',
				),
			),
			'slider_thumb_back_color' => array(
				'name' => 'thumb_background_color',
			),
			'slider_thumb_passive_color' => array(
				'name' => 'thumb_passive_color',
			),
			'slider_thumb_passive_color_trans' => array(
				'name' => 'thumb_passive_color_transparency',
			),
			'slider_thumb_height' => array(
				'name' => 'thumb_height',
			),
		);

		foreach ($options as $option) {
			$name = $option['name'];
			$value = $option['value'];
			$title = $option['title'];

			if (!isset($replaces[$name])) {
				continue;
			}

			$function_name = 'set_' . $replaces[$name]['name'];

			try {
				if ( isset( $replaces[ $name ], $replaces[ $name ]['name'], $replaces[ $name ]['replace'], $replaces[ $name ]['replace'][ $value ] ) && method_exists( 'Hugeit_Slider_Options', $function_name ) ) {
					$value = $replaces[ $name ]['replace'][ $value ];
					call_user_func( array( 'Hugeit_Slider_Options', $function_name ), $value, $title );
				} elseif ( isset( $replaces[ $name ], $replaces[ $name ]['name'] ) && ! isset( $replaces[ $name ]['replace'] ) ) {
					call_user_func( array( 'Hugeit_Slider_Options', $function_name ), $value, $title );
				}
			} catch ( Exception $e ) {

			}
		}
	}
}
