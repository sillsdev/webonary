<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

interface Hugeit_Slider_Slide_Post_Interface extends Hugeit_Slider_Slide_Interface {
	/**
	 * Get category(term) ID.
	 *
	 * @return int
	 */
	public function get_term_id();

	/**
	 * @return int
	 */
	public function get_show_title();

	/**
	 * @return int
	 */
	public function get_show_description();

	/**
	 * @return int
	 */
	public function get_go_to_post();

	/**
	 * @return int
	 */
	public function get_max_post_count();
}