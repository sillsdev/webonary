<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

interface Hugeit_Slider_Slide_Interface {
	/**
	 * Returns Slide ID.
	 *
	 * @return int
	 */
	public function get_id();

	/**
	 * Returns the slide's slider ID.
	 *
	 * @return int
	 */
	public function get_slider_id();

	/**
	 * @return int
	 */
	public function get_in_new_tab();

	/**
	 * Returns the slide type. 'image' for image slide, 'video' for post slide and 'post' for post slide.
	 *
	 * @return string
	 */
	public function get_type();

	/**
	 * Get current slide order in slider. The oder starts from 0.
	 *
	 * @return int
	 */
	public function get_order();
}