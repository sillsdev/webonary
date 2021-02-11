<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Hugeit_Slider_Helpers {

	/**
	 * @param $has_background
	 *
	 * @return bool
	 */
	public static function has_background( &$has_background ) {

		if ($has_background) {
			$has_background = false;

			return true;
		}

		$has_background = true;

		return false;
	}

	/**
	 * @param mixed $value
	 * @param mixed $true_value
	 * @param mixed $echo_text
	 * @param bool $strict_match
	 */
	public static function echo_on_match( $value, $true_value, $echo_text, $strict_match = false ) {
		$condition = $strict_match ? $value === $true_value : $value == $true_value;

		if ($condition) {
			echo $echo_text;
		}
	}

	/**
	 * @param string $url
	 *
	 * @return bool|string
	 */
	public static function youtube_or_vimeo( $url ) {
		if (preg_match('/^(https?\:\/\/)?(www\.youtube\.com|youtu\.?be)\/.+$/', $url)) {
			return 'youtube';
		} elseif (preg_match('/https:\/\/vimeo.com\/\d{8,12}(?=\b|\/)/', $url)) {
			return 'vimeo';
		} else {
			return false;
		}
	}
}