<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

final class Hugeit_Slider_Options implements Hugeit_Slider_Options_Interface {

	/**
	 * Prefix for option value.
	 *
	 * @var string
	 */
	private static $prefix = 'hugeit_slider_';

	/**
	 * Prefix for option title.
	 *
	 * @var string
	 */
	private static $prefix_for_title = 'hugeit_slider_title_for_';

	private static $crop_image;

	private static $title_color;

	private static $title_font_size;

	private static $description_color;

	private static $description_font_size;

	private static $title_position;

	private static $description_position;

	private static $title_border_size;

	private static $title_border_color;

	private static $title_border_radius;

	private static $description_border_size;

	private static $description_border_color;

	private static $description_border_radius;

	private static $slideshow_border_size;

	private static $slideshow_border_color;

	private static $slideshow_border_radius;

	private static $navigation_type;

	private static $navigation_position;

	private static $title_background_color;

	private static $description_background_color;

	private static $slider_background_color;

	private static $slider_background_color_transparency;

	private static $active_dot_color;

	private static $dots_color;

	private static $loading_icon_type;

	private static $description_width;

	private static $description_height;

	private static $description_background_transparency;

	private static $description_text_align;

	private static $title_width;

	private static $title_height;

	private static $title_background_transparency;

	private static $title_has_margin;

	private static $show_arrows;

	private static $title_text_align;

	private static $description_has_margin;

	private static $thumb_count_slides;

	private static $thumb_background_color;

	private static $thumb_passive_color;

	private static $thumb_passive_color_transparency;

	private static $thumb_height;
	
	private static $share_buttons;

	private static $share_buttons_facebook;

	private static $share_buttons_twitter;

	private static $share_buttons_gp;

	private static $share_buttons_pinterest;

	private static $share_buttons_linkedin;

	private static $share_buttons_tumblr;

	private static $share_buttons_style;

	private static $share_buttons_hover_style;

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_crop_image($with_title = false) {
		if ( ! $with_title ) {
			if (is_null(self::$crop_image)) {
				self::$crop_image = get_option( self::$prefix . 'crop_image' );
				return self::$crop_image;
			}

			return self::$crop_image;
		} else {
			return array(
				'value' => self::get_crop_image(),
				'title' => get_option(self::$prefix_for_title . 'crop_image')
			);
		}
	}

	/**
	 * @param int|bool $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_crop_image($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'crop_image', sanitize_text_field($title));
		}

		if ( $value === 'stretch' || $value === 'fill'  ) {
			$success = update_option(self::$prefix . 'crop_image', $value);
			if ($success) {
				self::$crop_image = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_title_color($with_title = false) {
		if ( ! $with_title ) {
			if (self::$title_color === NULL) {
				self::$title_color = get_option(self::$prefix . 'title_color');

				return self::$title_color;
			}

			return self::$title_color;
		} else {
			return array(
				'value' => self::get_title_color(),
				'title' => get_option( self::$prefix_for_title . 'title_color' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_title_color($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'title_color', sanitize_text_field($title));
		}

		if (ctype_xdigit($value) && strlen($value) === 6) {
			$success = update_option(self::$prefix . 'title_color', $value);
			if ($success) {
				self::$title_color = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_title_font_size($with_title = false) {
		if ( ! $with_title ) {
			if (self::$title_font_size === NULL) {
				self::$title_font_size = get_option(self::$prefix . 'title_font_size');

				return self::$title_font_size;
			}

			return self::$title_font_size;
		} else {
			return array(
				'value' => self::get_title_font_size(),
				'title' => get_option( self::$prefix_for_title . 'title_font_size' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_title_font_size($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'title_font_size', sanitize_text_field($title));
		}

		if (absint($value) > 0) {
			$success = update_option(self::$prefix . 'title_font_size', $value);
			if ($success) {
				self::$title_font_size = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_description_color($with_title = false) {
		if ( ! $with_title ) {
			if (self::$description_color === NULL) {
				self::$description_color = get_option(self::$prefix . 'description_color');

				return self::$description_color;
			}

			return self::$description_color;
		} else {
			return array(
				'value' => self::get_description_color(),
				'title' => get_option( self::$prefix_for_title . 'description_color' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_description_color($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'description_color', sanitize_text_field($title));
		}

		if (ctype_xdigit($value) && strlen($value) === 6) {
			$success = update_option(self::$prefix . 'description_color', $value);
			if ($success) {
				self::$description_color = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_description_font_size($with_title = false) {
		if ( ! $with_title ) {
			if (self::$description_font_size === NULL) {
				self::$description_font_size = get_option(self::$prefix . 'description_font_size');

				return self::$description_font_size;
			}

			return self::$description_font_size;
		} else {
			return array(
				'value' => self::get_description_font_size(),
				'title' => get_option( self::$prefix_for_title . 'description_font_size' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_description_font_size($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'description_font_size', sanitize_text_field($title));
		}

		if (absint($value) > 0) {
			$success = update_option(self::$prefix . 'description_font_size', $value);
			if ($success) {
				self::$description_font_size = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return string
	 */
	public static function get_title_position($with_title = false) {
		if ( ! $with_title ) {
			if (self::$title_position === NULL) {
				self::$title_position = get_option(self::$prefix . 'title_position');

				return self::$title_position;
			}

			return self::$title_position;
		} else {
			return array(
				'value' => self::get_title_position(),
				'title' => get_option( self::$prefix_for_title . 'title_position' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_title_position($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'title_position', sanitize_text_field($title));
		}

		$value = strval($value);

		if (strlen($value) === 2 && absint($value[0]) > 0 && absint($value[1]) > 0 && absint($value[0]) < 4 && absint($value[1]) < 4) {
			$value = absint($value);
			$success = update_option(self::$prefix . 'title_position', $value);
			if ($success) {
				self::$title_position = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_description_position($with_title = false) {
		if ( ! $with_title ) {
			if (self::$description_position === NULL) {
				self::$description_position = get_option(self::$prefix . 'description_position');

				return self::$description_position;
			}

			return self::$description_position;
		} else {
			return array(
				'value' => self::get_description_position(),
				'title' => get_option( self::$prefix_for_title . 'description_position' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_description_position($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'description_position', sanitize_text_field($title));
		}

		$value = strval($value);

		if (strlen($value) === 2 && absint($value[0]) > 0 && absint($value[1]) > 0 && absint($value[0]) < 4 && absint($value[1]) < 4) {
			$value = absint($value);
			$success = update_option(self::$prefix . 'description_position', $value);
			if ($success) {
				self::$description_position = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_title_border_size($with_title = false) {
		if ( ! $with_title ) {
			if (self::$title_border_size === NULL) {
				self::$title_border_size = get_option(self::$prefix . 'title_border_size');

				return self::$title_border_size;
			}

			return self::$title_border_size;
		} else {
			return array(
				'value' => self::get_title_border_size(),
				'title' => get_option( self::$prefix_for_title . 'title_border_size' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_title_border_size($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'title_border_size', sanitize_text_field($title));
		}

		$value = absint($value);

		$success = update_option(self::$prefix . 'title_border_size', $value);
		if ($success) {
			self::$title_border_size = $value;
		}

		return $success;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_title_border_color($with_title = false) {
		if ( ! $with_title ) {
			if (self::$title_border_color === NULL) {
				self::$title_border_color = get_option(self::$prefix . 'title_border_color');

				return self::$title_border_color;
			}

			return self::$title_border_color;
		} else {
			return array(
				'value' => self::get_title_border_color(),
				'title' => get_option( self::$prefix_for_title . 'title_border_color' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_title_border_color($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'title_border_color', sanitize_text_field($title));
		}

		if (ctype_xdigit($value) && strlen($value) === 6) {
			$success = update_option(self::$prefix . 'title_border_color', $value);
			if ($success) {
				self::$title_border_color = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_title_border_radius($with_title = false) {
		if ( ! $with_title ) {
			if (self::$title_border_radius === NULL) {
				self::$title_border_radius = get_option(self::$prefix . 'title_border_radius');

				return self::$title_border_radius;
			}

			return self::$title_border_radius;
		} else {
			return array(
				'value' => self::get_title_border_radius(),
				'title' => get_option( self::$prefix_for_title . 'title_border_radius' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_title_border_radius($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'title_border_radius', sanitize_text_field($title));
		}

		$success = update_option(self::$prefix . 'title_border_radius', absint($value));
		if ($success) {
			self::$title_border_radius = absint($value);
		}

		return $success;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_description_border_size($with_title = false) {
		if ( ! $with_title ) {
			if (self::$description_border_size === NULL) {
				self::$description_border_size = get_option(self::$prefix . 'description_border_size');

				return self::$description_border_size;
			}

			return self::$description_border_size;
		} else {
			return array(
				'value' => self::get_description_border_size(),
				'title' => get_option( self::$prefix_for_title . 'description_border_size' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_description_border_size($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'description_border_size', sanitize_text_field($title));
		}

		$success = update_option(self::$prefix . 'description_border_size', absint($value));
		if ($success) {
			self::$description_border_size = absint($value);
		}

		return $success;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return mixed
	 */
	public static function get_description_border_color($with_title = false) {
		if ( ! $with_title ) {
			if (self::$description_border_color === NULL) {
				self::$description_border_color = get_option(self::$prefix . 'description_border_color');

				return self::$description_border_color;
			}

			return self::$description_border_color;
		} else {
			return array(
				'value' => self::get_description_border_color(),
				'title' => get_option(self::$prefix_for_title . 'description_border_size')
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_description_border_color($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'description_border_color', sanitize_text_field($title));
		}

		if (ctype_xdigit($value) && strlen($value) === 6) {
			$success = update_option(self::$prefix . 'description_border_color', $value);
			if ($success) {
				self::$description_border_color = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return mixed
	 */
	public static function get_description_border_radius($with_title = false) {
		if ( ! $with_title ) {
			if (self::$description_border_radius === NULL) {
				self::$description_border_radius = get_option(self::$prefix . 'description_border_radius');

				return self::$description_border_radius;
			}

			return self::$description_border_radius;
		} else {
			return array(
				'value' => self::get_description_border_radius(),
				'title' => get_option( self::$prefix_for_title . 'description_border_radius' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_description_border_radius($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'description_border_radius', sanitize_text_field($title));
		}

		$success = update_option(self::$prefix . 'description_border_radius', absint($value));
		if ($success) {
			self::$description_border_radius = absint($value);
		}

		return $success;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return mixed
	 */
	public static function get_slideshow_border_size($with_title = false) {
		if ( ! $with_title ) {
			if (self::$slideshow_border_size === NULL) {
				self::$slideshow_border_size = get_option(self::$prefix . 'slideshow_border_size');

				return self::$slideshow_border_size;
			}

			return self::$slideshow_border_size;
		} else {
			return array(
				'value' => self::get_slideshow_border_size(),
				'title' => get_option( self::$prefix_for_title . 'slideshow_border_size' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_slideshow_border_size($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'slideshow_border_size', sanitize_text_field($title));
		}

		$success = update_option(self::$prefix . 'slideshow_border_size', absint($value));
		if ($success) {
			self::$slideshow_border_size = absint($value);
		}

		return $success;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return mixed
	 */
	public static function get_slideshow_border_color($with_title = false) {
		if ( ! $with_title ) {
			if (self::$slideshow_border_color === NULL) {
				self::$slideshow_border_color = get_option(self::$prefix . 'slideshow_border_color');

				return self::$slideshow_border_color;
			}

			return self::$slideshow_border_color;
		} else {
			return array(
				'value' => self::get_slideshow_border_color(),
				'title' => get_option( self::$prefix_for_title . 'slideshow_border_color' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_slideshow_border_color($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'slideshow_border_color', sanitize_text_field($title));
		}

		if (ctype_xdigit($value) && strlen($value) === 6) {
			$success = update_option(self::$prefix . 'slideshow_border_color', $value);

			if ($success) {
				self::$slideshow_border_color = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return mixed
	 */
	public static function get_slideshow_border_radius($with_title = false) {
		if ( ! $with_title ) {
			if (self::$slideshow_border_radius === NULL) {
				self::$slideshow_border_radius = get_option(self::$prefix . 'slideshow_border_radius');

				return self::$slideshow_border_radius;
			}

			return self::$slideshow_border_radius;
		} else {
			return array(
				'value' => self::get_slideshow_border_radius(),
				'title' => get_option( self::$prefix_for_title . 'slideshow_border_radius' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_slideshow_border_radius($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'slideshow_border_radius', sanitize_text_field($title));
		}

		$success = update_option(self::$prefix . 'slideshow_border_radius', absint($value));

		if ($success) {
			self::$slideshow_border_radius = absint($value);
		}

		return $success;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return mixed
	 */
	public static function get_navigation_type($with_title = false) {
		if ( ! $with_title ) {
			if (self::$navigation_type === NULL) {
				self::$navigation_type = get_option(self::$prefix . 'navigation_type');

				return self::$navigation_type;
			}

			return self::$navigation_type;
		} else {
			return array(
				'value' => self::get_navigation_type(),
				'title' => get_option( self::$prefix_for_title . 'navigation_type' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_navigation_type($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'navigation_type', sanitize_text_field($title));
		}

		if ( absint( $value ) ) {
			$success = update_option( self::$prefix . 'navigation_type', absint( $value ) );

			if ($success) {
				self::$navigation_type = absint( $value );
			}

			return $success;
		} else {
			return false;
		}
	}

	/**
	 * @values ['top', 'bottom']
	 * @param bool $with_title
	 *
	 * @return string
	 */
	public static function get_navigation_position($with_title = false) {
		if ( ! $with_title ) {
			if (self::$navigation_position === NULL) {
				self::$navigation_position = get_option(self::$prefix . 'navigation_position');

				return self::$navigation_position;
			}

			return self::$navigation_position;
		} else {
			return array(
				'value' => self::get_navigation_position(),
				'title' => get_option( self::$prefix_for_title . 'navigation_position' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_navigation_position($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'navigation_position', sanitize_text_field($title));
		}

		if ($value !== 'top' && $value !== 'bottom') {
			return false;
		}

		$success = update_option(self::$prefix . 'navigation_position', $value);
		if ($success) {
			self::$navigation_position = $value;
		}

		return $success;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_title_background_color($with_title = false) {
		if ( ! $with_title ) {
			if (self::$title_background_color === NULL) {
				self::$title_background_color = get_option(self::$prefix . 'title_background_color');

				return self::$title_background_color;
			}

			return self::$title_background_color;
		} else {
			return array(
				'value' => self::get_title_background_color(),
				'title' => get_option( self::$prefix_for_title . 'title_background_color' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_title_background_color($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'title_background_color', sanitize_text_field($title));
		}

		if (ctype_xdigit($value) && strlen($value) === 6) {
			$success = update_option(self::$prefix . 'title_background_color', $value);
			if ($success) {
				self::$title_background_color = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_description_background_color($with_title = false) {
		if ( ! $with_title ) {
			if (self::$description_background_color === NULL) {
				self::$description_background_color = get_option(self::$prefix . 'description_background_color');

				return self::$description_background_color;
			}

			return self::$description_background_color;
		} else {
			return array(
				'value' => self::get_description_background_color(),
				'title' => get_option( self::$prefix_for_title . 'description_background_color' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_description_background_color($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'description_background_color', sanitize_text_field($title));
		}

		if (ctype_xdigit($value) && strlen($value) === 6) {
			$success = update_option(self::$prefix . 'description_background_color', $value);
			if ($success) {
				self::$description_background_color = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return mixed
	 */
	public static function get_slider_background_color($with_title = false) {
		if ( ! $with_title ) {
			if (self::$slider_background_color === NULL) {
				self::$slider_background_color = get_option(self::$prefix . 'slider_background_color');

				return self::$slider_background_color;
			}

			return self::$slider_background_color;
		} else {
			return array(
				'value' => self::get_slider_background_color(),
				'title' => get_option( self::$prefix_for_title . 'slider_background_color' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_slider_background_color($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'slider_background_color', sanitize_text_field($title));
		}

		if (ctype_xdigit($value) && strlen($value) === 6) {
			$success = update_option(self::$prefix . 'slider_background_color', $value);
			if ($success) {
				self::$slider_background_color = $value;
			}

			return $success;
		}

		return false;
	}

	public static function get_slider_background_color_transparency($with_title = false) {
		if ( ! $with_title ) {
			if (self::$slider_background_color_transparency === NULL) {
				self::$slider_background_color_transparency = get_option(self::$prefix . 'slider_background_color_transparency');

				return self::$slider_background_color_transparency;
			}

			return self::$slider_background_color_transparency;
		} else {
			return array(
				'value' => self::get_slider_background_color_transparency(),
				'title' => get_option( self::$prefix_for_title . 'slider_background_color_transparency' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_slider_background_color_transparency($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'slider_background_color_transparency', sanitize_text_field($title));
		}

		$value = round($value/100, 2);

		if ( $value >= 0 && $value <= 1 ) {
			$success = update_option(self::$prefix . 'slider_background_color_transparency', $value);
			if ($success) {
				self::$slider_background_color_transparency = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return mixed
	 */
	public static function get_active_dot_color($with_title = false) {
		if ( ! $with_title ) {
			if (self::$active_dot_color === NULL) {
				self::$active_dot_color = get_option(self::$prefix . 'active_dot_color');

				return self::$active_dot_color;
			}

			return self::$active_dot_color;
		} else {
			return array(
				'value' => self::get_active_dot_color(),
				'title' => get_option( self::$prefix_for_title . 'active_dot_color' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_active_dot_color($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'active_dot_color', sanitize_text_field($title));
		}

		if (ctype_xdigit($value) && strlen($value) === 6) {
			$success = update_option(self::$prefix . 'active_dot_color', $value);
			if ($success) {
				self::$active_dot_color = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return mixed
	 */
	public static function get_dots_color($with_title = false) {
		if ( ! $with_title ) {
			if (self::$dots_color === NULL) {
				self::$dots_color = get_option(self::$prefix . 'dot_color');

				return self::$dots_color;
			}

			return self::$dots_color;
		} else {
			return array(
				'value' => self::get_dots_color(),
				'title' => get_option( self::$prefix_for_title . 'dot_color' )
			);
		}
	}

	/**
	 * @param $value
	 *
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_dots_color($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'dot_color', sanitize_text_field($title));
		}

		if ( ctype_xdigit( $value ) && strlen( $value ) === 6 ) {
			$success = update_option( self::$prefix . 'dot_color', $value );

			if ( $success ) {
				self::$dots_color = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param $with_title
	 *
	 * @return array|int
	 */
	public static function get_loading_icon_type($with_title = false) {
		if ( ! $with_title ) {
			if (self::$loading_icon_type === NULL) {
				self::$loading_icon_type = get_option(self::$prefix . 'loading_icon_type');

				return self::$loading_icon_type;
			}

			return self::$loading_icon_type;
		} else {
			return array(
				'value' => self::get_loading_icon_type(),
				'title' => get_option( self::$prefix_for_title . 'loading_icon_type' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_loading_icon_type( $value, $title = NULL ) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'loading_icon_type', sanitize_text_field($title));
		}

		if (absint($value)) {
			$success = update_option(self::$prefix . 'loading_icon_type', $value);
			if ($success) {
				self::$loading_icon_type = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_description_width($with_title = false) {
		if ( ! $with_title ) {
			if (self::$description_width === NULL) {
				self::$description_width = get_option(self::$prefix . 'description_width');

				return self::$description_width;
			}

			return self::$description_width;
		} else {
			return array(
				'value' => self::get_description_width(),
				'title' => get_option( self::$prefix_for_title . 'description_width' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_description_width($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'description_width', sanitize_text_field($title));
		}

		$value = absint($value);

		if ($value) {
			$success = update_option(self::$prefix . 'description_width', $value);
			if ($success) {
				self::$description_width = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_description_height($with_title = false) {
		if ( ! $with_title ) {
			if (self::$description_height === NULL) {
				self::$description_height = get_option(self::$prefix . 'description_height');

				return self::$description_height;
			}

			return self::$description_height;
		} else {
			return array(
				'value' => self::get_description_height(),
				'title' => get_option( self::$prefix_for_title . 'description_height' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_description_height($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'description_height', sanitize_text_field($title));
		}

		$value = absint($value);

		if ($value) {
			$success = update_option(self::$prefix . 'description_height', $value);
			if ($success) {
				self::$description_height = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_description_background_transparency($with_title = false) {
		if ( ! $with_title ) {
			if (self::$description_background_transparency === NULL) {
				self::$description_background_transparency = get_option(self::$prefix . 'description_background_transparency');

				return self::$description_background_transparency;
			}

			return self::$description_background_transparency;
		} else {
			return array(
				'value' => self::get_description_background_transparency(),
				'title' => get_option( self::$prefix_for_title . 'description_background_transparency' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_description_background_transparency($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'description_background_transparency', sanitize_text_field($title));
		}

		$value = round($value/100, 2);

		if ( $value >= 0 && $value <= 1 ) {
			$success = update_option(self::$prefix . 'description_background_transparency', $value);
			if ($success) {
				self::$description_background_transparency = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_description_text_align($with_title = false) {
		if ( ! $with_title ) {
			if (self::$description_text_align === NULL) {
				self::$description_text_align = get_option(self::$prefix . 'description_text_align');

				return self::$description_text_align;
			}

			return self::$description_text_align;
		} else {
			return array(
				'value' => self::get_description_text_align(),
				'title' => get_option( self::$prefix_for_title . 'description_text_align' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_description_text_align($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'description_text_align', sanitize_text_field($title));
		}

		if ( in_array($value, array('center', 'left', 'right', 'justify')) ) {
			$success = update_option(self::$prefix . 'description_text_align', $value);
			if ($success) {
				self::$description_text_align = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_title_width($with_title = false) {
		if ( ! $with_title ) {
			if (self::$title_width === NULL) {
				self::$title_width = get_option(self::$prefix . 'title_width');

				return self::$title_width;
			}

			return self::$title_width;
		} else {
			return array(
				'value' => self::get_title_width(),
				'title' => get_option( self::$prefix_for_title . 'title_width' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_title_width($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'title_width', sanitize_text_field($title));
		}

		$value = absint($value);

		if ($value) {
			$success = update_option(self::$prefix . 'title_width', $value);
			if ($success) {
				self::$title_width = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array|float
	 */
	public static function get_title_height($with_title = false) {
		if ( ! $with_title ) {
			if (self::$title_height === NULL) {
				self::$title_height = null === get_option(self::$prefix . 'title_height') ? false : (float)get_option(self::$prefix . 'title_height');

				return self::$title_height;
			}

			return self::$title_height;
		} else {
			return array(
				'value' => self::get_title_height(),
				'title' => get_option( self::$prefix_for_title . 'title_height' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_title_height($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'title_height', sanitize_text_field($title));
		}

		$value = abs((float)$value);

		if ($value) {
			$success = update_option(self::$prefix . 'title_height', $value);
			if ($success) {
				self::$title_height = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array|float
	 */
	public static function get_title_background_transparency($with_title = false) {
		if ( ! $with_title ) {
			if (self::$title_background_transparency === NULL) {
				self::$title_background_transparency = null === get_option(self::$prefix . 'title_background_transparency', null) ? false : (float)get_option(self::$prefix . 'title_background_transparency');

				return self::$title_background_transparency;
			}

			return self::$title_background_transparency;
		} else {
			return array(
				'value' => self::get_title_background_transparency(),
				'title' => get_option( self::$prefix_for_title . 'title_background_transparency' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_title_background_transparency($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'title_background_transparency', sanitize_text_field($title));
		}

		$value = round($value/100, 2);

		if ( $value >= 0 && $value <= 1 ) {
			$success = update_option(self::$prefix . 'title_background_transparency', $value);
			if ($success) {
				self::$title_background_transparency = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array|string
	 */
	public static function get_title_text_align($with_title = false) {
		if ( ! $with_title ) {
			if (self::$title_text_align === NULL) {
				self::$title_text_align = get_option(self::$prefix . 'title_text_align');

				return self::$title_text_align;
			}

			return self::$title_text_align;
		} else {
			return array(
				'value' => self::get_title_text_align(),
				'title' => get_option( self::$prefix_for_title . 'title_text_align' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_title_text_align($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'title_text_align', sanitize_text_field($title));
		}

		if ( in_array( $value, array( 'left', 'right', 'center', 'justify' ) ) ) {
			$success = update_option( self::$prefix . 'title_text_align', $value );
			if ($success) {
				self::$title_text_align = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_title_has_margin( $with_title = false ) {
		if ( ! $with_title ) {
			if (self::$title_has_margin === NULL) {
				self::$title_has_margin = null === get_option(self::$prefix . 'title_has_margin', null) ? false : (int)get_option(self::$prefix . 'title_has_margin');

				return self::$title_has_margin;
			}

			return self::$title_has_margin;
		} else {
			return array(
				'value' => self::get_title_has_margin(),
				'title' => get_option( self::$prefix_for_title . 'title_has_margin' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_title_has_margin( $value, $title = NULL ) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'title_has_margin', sanitize_text_field($title));
		}

		if ($value == 1 || $value == 0) {
			$success = update_option(self::$prefix . 'title_has_margin', (int)$value);
			if ($success) {
				self::$title_has_margin = (int)$value;
			}

			return $success;
		}

		return false;
	}
	
	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_share_buttons( $with_title = false ) {
		if ( ! $with_title ) {
			if (self::$share_buttons === NULL) {
				self::$share_buttons = null === get_option(self::$prefix . 'share_buttons', null) ? false : (int)get_option(self::$prefix . 'share_buttons');

				return self::$share_buttons;
			}

			return self::$share_buttons;
		} else {
			return array(
				'value' => self::get_share_buttons(),
				'title' => get_option( self::$prefix_for_title . 'share_buttons' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_share_buttons( $value, $title = NULL ) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'share_buttons', sanitize_text_field($title));
		}

		if ($value == 1 || $value == 0) {
			$success = update_option(self::$prefix . 'share_buttons', (int)$value);
			if ($success) {
				self::$share_buttons = (int)$value;
			}

			return $success;
		}

		return false;
	}


	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_share_buttons_facebook( $with_title = false ) {
		if ( ! $with_title ) {
			if (self::$share_buttons_facebook === NULL) {
				self::$share_buttons_facebook = null === get_option(self::$prefix . 'share_buttons_facebook', null) ? false : (int)get_option(self::$prefix . 'share_buttons_facebook');

				return self::$share_buttons_facebook;
			}

			return self::$share_buttons_facebook;
		} else {
			return array(
				'value' => self::get_share_buttons_facebook(),
				'title' => get_option( self::$prefix_for_title . 'share_buttons_facebook' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_share_buttons_facebook( $value, $title = NULL ) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'share_buttons_facebook', sanitize_text_field($title));
		}

		if ($value == 1 || $value == 0) {
			$success = update_option(self::$prefix . 'share_buttons_facebook', (int)$value);
			if ($success) {
				self::$share_buttons_facebook = (int)$value;
			}

			return $success;
		}

		return false;
	}


	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_share_buttons_twitter( $with_title = false ) {
		if ( ! $with_title ) {
			if (self::$share_buttons_twitter === NULL) {
				self::$share_buttons_twitter = null === get_option(self::$prefix . 'share_buttons_twitter', null) ? false : (int)get_option(self::$prefix . 'share_buttons_twitter');

				return self::$share_buttons_twitter;
			}

			return self::$share_buttons_twitter;
		} else {
			return array(
				'value' => self::get_share_buttons_twitter(),
				'title' => get_option( self::$prefix_for_title . 'share_buttons_twitter' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_share_buttons_twitter( $value, $title = NULL ) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'share_buttons_twitter', sanitize_text_field($title));
		}

		if ($value == 1 || $value == 0) {
			$success = update_option(self::$prefix . 'share_buttons_twitter', (int)$value);
			if ($success) {
				self::$share_buttons_twitter = (int)$value;
			}

			return $success;
		}

		return false;
	}


	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_share_buttons_gp( $with_title = false ) {
		if ( ! $with_title ) {
			if (self::$share_buttons_gp === NULL) {
				self::$share_buttons_gp = null === get_option(self::$prefix . 'share_buttons_gp', null) ? false : (int)get_option(self::$prefix . 'share_buttons_gp');

				return self::$share_buttons_gp;
			}

			return self::$share_buttons_gp;
		} else {
			return array(
				'value' => self::get_share_buttons_gp(),
				'title' => get_option( self::$prefix_for_title . 'share_buttons_gp' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_share_buttons_gp( $value, $title = NULL ) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'share_buttons_gp', sanitize_text_field($title));
		}

		if ($value == 1 || $value == 0) {
			$success = update_option(self::$prefix . 'share_buttons_gp', (int)$value);
			if ($success) {
				self::$share_buttons_gp = (int)$value;
			}

			return $success;
		}

		return false;
	}


	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_share_buttons_pinterest( $with_title = false ) {
		if ( ! $with_title ) {
			if (self::$share_buttons_pinterest === NULL) {
				self::$share_buttons_pinterest = null === get_option(self::$prefix . 'share_buttons_pinterest', null) ? false : (int)get_option(self::$prefix . 'share_buttons_pinterest');

				return self::$share_buttons_pinterest;
			}

			return self::$share_buttons_pinterest;
		} else {
			return array(
				'value' => self::get_share_buttons_pinterest(),
				'title' => get_option( self::$prefix_for_title . 'share_buttons_pinterest' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_share_buttons_pinterest( $value, $title = NULL ) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'share_buttons_pinterest', sanitize_text_field($title));
		}

		if ($value == 1 || $value == 0) {
			$success = update_option(self::$prefix . 'share_buttons_pinterest', (int)$value);
			if ($success) {
				self::$share_buttons_pinterest = (int)$value;
			}

			return $success;
		}

		return false;
	}


	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_share_buttons_linkedin( $with_title = false ) {
		if ( ! $with_title ) {
			if (self::$share_buttons_linkedin === NULL) {
				self::$share_buttons_linkedin = null === get_option(self::$prefix . 'share_buttons_linkedin', null) ? false : (int)get_option(self::$prefix . 'share_buttons_linkedin');

				return self::$share_buttons_linkedin;
			}

			return self::$share_buttons_linkedin;
		} else {
			return array(
				'value' => self::get_share_buttons_linkedin(),
				'title' => get_option( self::$prefix_for_title . 'share_buttons_linkedin' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_share_buttons_linkedin( $value, $title = NULL ) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'share_buttons_linkedin', sanitize_text_field($title));
		}

		if ($value == 1 || $value == 0) {
			$success = update_option(self::$prefix . 'share_buttons_linkedin', (int)$value);
			if ($success) {
				self::$share_buttons_linkedin = (int)$value;
			}

			return $success;
		}

		return false;
	}


	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_share_buttons_tumblr( $with_title = false ) {
		if ( ! $with_title ) {
			if (self::$share_buttons_tumblr === NULL) {
				self::$share_buttons_tumblr = null === get_option(self::$prefix . 'share_buttons_tumblr', null) ? false : (int)get_option(self::$prefix . 'share_buttons_tumblr');

				return self::$share_buttons_tumblr;
			}

			return self::$share_buttons_tumblr;
		} else {
			return array(
				'value' => self::get_share_buttons_tumblr(),
				'title' => get_option( self::$prefix_for_title . 'share_buttons_tumblr' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_share_buttons_tumblr( $value, $title = NULL ) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'share_buttons_tumblr', sanitize_text_field($title));
		}

		if ($value == 1 || $value == 0) {
			$success = update_option(self::$prefix . 'share_buttons_tumblr', (int)$value);
			if ($success) {
				self::$share_buttons_tumblr = (int)$value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_share_buttons_style($with_title = false) {
		if ( ! $with_title ) {
			if (self::$share_buttons_style === NULL) {
				self::$share_buttons_style = get_option(self::$prefix . 'share_buttons_style');

				return self::$share_buttons_style;
			}

			return self::$share_buttons_style;
		} else {
			return array(
				'value' => self::get_share_buttons_style(),
				'title' => get_option( self::$prefix_for_title . 'share_buttons_style' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_share_buttons_style($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'share_buttons_style', sanitize_text_field($title));
		}

		if ( in_array($value, array('circle', 'square')) ) {
			$success = update_option(self::$prefix . 'share_buttons_style', $value);
			if ($success) {
				self::$share_buttons_style = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_share_buttons_hover_style($with_title = false) {
		if ( ! $with_title ) {
			if (self::$share_buttons_hover_style === NULL) {
				self::$share_buttons_hover_style = get_option(self::$prefix . 'share_buttons_hover_style');

				return self::$share_buttons_hover_style;
			}

			return self::$share_buttons_hover_style;
		} else {
			return array(
				'value' => self::get_share_buttons_hover_style(),
				'title' => get_option( self::$prefix_for_title . 'share_buttons_hover_style' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_share_buttons_hover_style($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'share_buttons_hover_style', sanitize_text_field($title));
		}

		if ( in_array($value, array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15')) ) {
			$success = update_option(self::$prefix . 'share_buttons_hover_style', $value);
			if ($success) {
				self::$share_buttons_hover_style = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array|int
	 */
	public static function get_description_has_margin( $with_title = false ) {
		if ( ! $with_title ) {
			if (self::$description_has_margin === NULL) {
				self::$description_has_margin = null === get_option(self::$prefix . 'description_has_margin', null) ? false : (int)get_option(self::$prefix . 'description_has_margin');

				return self::$description_has_margin;
			}

			return self::$description_has_margin;
		} else {
			return array(
				'value' => self::get_description_has_margin(),
				'title' => get_option( self::$prefix_for_title . 'description_has_margin' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_description_has_margin( $value, $title = NULL ) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'description_has_margin', sanitize_text_field($title));
		}

		if ($value == 1 || $value == 0) {
			$success = update_option(self::$prefix . 'description_has_margin', (int)$value);
			if ($success) {
				self::$description_has_margin = (int)$value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_show_arrows( $with_title = false ) {
		if ( ! $with_title ) {
			if (self::$show_arrows === NULL) {
				self::$show_arrows = null === get_option(self::$prefix . 'show_arrows', null) ? false : (int)get_option(self::$prefix . 'show_arrows');

				return self::$show_arrows;
			}

			return self::$show_arrows;
		} else {
			return array(
				'value' => self::get_show_arrows(),
				'title' => get_option( self::$prefix_for_title . 'show_arrows' )
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_show_arrows( $value, $title = NULL ) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'show_arrows', sanitize_text_field($title));
		}

		if ($value == 1 || $value == 0) {
			$success = update_option(self::$prefix . 'show_arrows', (int)$value);
			if ($success) {
				self::$show_arrows = (int)$value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_thumb_count_slides( $with_title = false ) {
		if (!$with_title) {
			if (self::$thumb_count_slides === NULL) {
				self::$thumb_count_slides = null === get_option(self::$prefix . 'thumb_count_slides', null) ? false : (int)get_option(self::$prefix . 'thumb_count_slides');

				return self::$thumb_count_slides;
			}

			return self::$thumb_count_slides;
		} else {
			return array(
				'value' => self::get_thumb_count_slides(),
				'title' => get_option(self::$prefix . 'thumb_count_slides')
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool|int
	 */
	public static function set_thumb_count_slides( $value, $title = NULL ) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'thumb_count_slides', sanitize_text_field($title));
		}

		$value = absint($value);

		if ($value >= 0 && $value < 999) {
			$success = update_option(self::$prefix . 'thumb_count_slides', $value);

			if ($success) {
				self::$thumb_count_slides = $value;
			}

			return $value;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array|string
	 */
	public static function get_thumb_background_color( $with_title = false ) {
		if (!$with_title) {
			if (self::$thumb_background_color === NULL) {
				self::$thumb_background_color = get_option(self::$prefix . 'thumb_background_color');

				return self::$thumb_background_color;
			}

			return self::$thumb_background_color;
		} else {
			return array(
				'value' => self::get_thumb_background_color(),
				'title' => get_option(self::$prefix . 'thumb_background_color')
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_thumb_background_color( $value, $title = NULL ) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'thumb_count_slides', sanitize_text_field($title));
		}

		if ( ctype_xdigit( $value ) && strlen( $value ) === 6 ) {
			$success = update_option( self::$prefix . 'thumb_background_color', $value );

			if ( $success ) {
				self::$thumb_background_color = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array|string
	 */
	public static function get_thumb_passive_color($with_title = false) {
		if (!$with_title) {
			if (self::$thumb_passive_color === NULL) {
				self::$thumb_passive_color = get_option(self::$prefix . 'thumb_passive_color');

				return self::$thumb_passive_color;
			}

			return self::$thumb_passive_color;
		} else {
			return array(
				'value' => self::get_thumb_passive_color(),
				'title' => get_option(self::$prefix . 'thumb_passive_color')
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_thumb_passive_color($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'thumb_passive_color', sanitize_text_field($title));
		}

		if ( ctype_xdigit( $value ) && strlen( $value ) === 6 ) {
			$success = update_option( self::$prefix . 'thumb_passive_color', $value );

			if ( $success ) {
				self::$thumb_passive_color = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array|float
	 */
	public static function get_thumb_passive_color_transparency($with_title = false) {
		if (!$with_title) {
			if (self::$thumb_passive_color_transparency === NULL) {
				self::$thumb_passive_color_transparency = null === get_option(self::$prefix . 'thumb_passive_color_transparency') ? false : (float)get_option(self::$prefix . 'thumb_passive_color_transparency');

				return self::$thumb_passive_color_transparency;
			}

			return self::$thumb_passive_color_transparency;
		} else {
			return array(
				'value' => self::get_thumb_passive_color_transparency(),
				'title' => get_option(self::$prefix . 'thumb_passive_color_transparency')
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_thumb_passive_color_transparency($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'thumb_passive_color_transparency', sanitize_text_field($title));
		}

		$value = round($value/100, 2);

		if ( $value >= 0 && $value <= 1 ) {
			$success = update_option(self::$prefix . 'thumb_passive_color_transparency', $value);
			if ($success) {
				self::$thumb_passive_color_transparency = $value;
			}

			return $success;
		}

		return false;
	}

	/**
	 * @param bool $with_title
	 *
	 * @return array|int
	 */
	public static function get_thumb_height($with_title = false) {
		if (!$with_title) {
			if (self::$thumb_height === NULL) {
				self::$thumb_height = null === get_option(self::$prefix . 'thumb_height', null) ? false : (int)get_option(self::$prefix . 'thumb_height');

				return self::$thumb_height;
			}

			return self::$thumb_height;
		} else {
			return array(
				'value' => self::get_thumb_height(),
				'title' => get_option(self::$prefix . 'thumb_height')
			);
		}
	}

	/**
	 * @param $value
	 * @param null $title
	 *
	 * @return bool
	 */
	public static function set_thumb_height($value, $title = NULL) {
		if ($title !== NULL) {
			update_option(self::$prefix_for_title . 'thumb_height', sanitize_text_field($title));
		}

		$value = absint($value);

		if ($value) {
			$success = update_option(self::$prefix . 'thumb_height', $value);
			if ($success) {
				self::$thumb_height = $value;
			}

			return $success;
		}

		return false;
	}
}