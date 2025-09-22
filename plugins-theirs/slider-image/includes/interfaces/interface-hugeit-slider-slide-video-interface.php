<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

interface Hugeit_Slider_Slide_Video_Interface extends Hugeit_Slider_Slide_Interface {
	/**
	 * @return string
	 */
	public function get_url();

	/**
	 * @return int
	 */
	public function get_quality();

	/**
	 * Return video volume value. Minimum value is 0, maximum 1.
	 *
	 * @return int
	 */
	public function get_volume();

	/**
	 * @return int
	 */
	public function get_show_controls();

	/**
	 * @return int
	 */
	public function get_show_info();

	/**
	 * This option is only for vimeo videos.
	 * Returns HEX number string WITHOUT '#' symbol.
	 *
	 * @return string
	 */
	public function get_control_color();

	/**
	 * @return string
	 */
	public function get_thumbnail_url();

	/**
	 * Returns video site. Either 'youtube' or 'vimeo'. False for other sites.
	 *
	 * @return string|bool
	 */
	public function get_site();
}