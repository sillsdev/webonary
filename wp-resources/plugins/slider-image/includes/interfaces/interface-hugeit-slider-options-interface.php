<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

interface Hugeit_Slider_Options_Interface {
	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_crop_image( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_title_color( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_title_font_size( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_description_color( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_description_font_size( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return string
	 */
	public static function get_title_position( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_description_position( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_title_border_size( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_title_border_color( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_title_border_radius( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_description_border_size( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return mixed
	 */
	public static function get_description_border_color( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return mixed
	 */
	public static function get_description_border_radius( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return mixed
	 */
	public static function get_slideshow_border_size( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return mixed
	 */
	public static function get_slideshow_border_color( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return mixed
	 */
	public static function get_slideshow_border_radius( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return mixed
	 */
	public static function get_navigation_type( $with_title = false );

	/**
	 * @values ['top', 'bottom']
	 * @param bool $with_title
	 *
	 * @return string
	 */
	public static function get_navigation_position( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_title_background_color( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array
	 */
	public static function get_description_background_color( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return mixed
	 */
	public static function get_slider_background_color( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return mixed
	 */
	public static function get_active_dot_color( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return mixed
	 */
	public static function get_dots_color( $with_title = false );

	/**
	 * @param $with_title
	 *
	 * @return array|int
	 */
	public static function get_loading_icon_type( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_description_width( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_description_height( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_description_background_transparency( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_description_text_align( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_title_width( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array|float
	 */
	public static function get_title_height( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array|float
	 */
	public static function get_title_background_transparency( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array|string
	 */
	public static function get_title_text_align( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_title_has_margin( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array|int
	 */
	public static function get_description_has_margin( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_show_arrows( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array|mixed
	 */
	public static function get_thumb_count_slides( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array|string
	 */
	public static function get_thumb_background_color( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array|string
	 */
	public static function get_thumb_passive_color( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array|float
	 */
	public static function get_thumb_passive_color_transparency( $with_title = false );

	/**
	 * @param bool $with_title
	 *
	 * @return array|int
	 */
	public static function get_thumb_height( $with_title = false );
}