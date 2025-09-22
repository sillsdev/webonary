<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

interface Hugeit_Slider_Slide_Image_Interface extends Hugeit_Slider_Slide_Interface {
	/**
	 * @return string
	 */
	public function get_title();

	/**
	 * @return string
	 */
	public function get_description();

	/**
	 * @return string
	 */
	public function get_url();

	/**
	 * Returns slide image ID (image is attachment).
	 *
	 * @return int
	 */
	public function get_attachment_id();
}