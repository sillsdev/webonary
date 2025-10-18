<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Hugeit_Slider_Slide implements Hugeit_Slider_Slide_Interface {

	/**
	 * Slide ID.
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * Slider ID in which current slide is.
	 *
	 * @var int
	 */
	protected $slider_id;

	/**
	 * Open resource in new tab or not. 1 if you want to open in new tab, 0 otherwise.
	 *
	 * @var int
	 */
	protected $in_new_tab;

	/**
	 * Slide type.
	 *
	 * @values ['image', 'video', 'post']
	 * @var string
	 */
	protected $type;

	/**
	 * Order in slider. First element.
	 *
	 * @var int
	 */
	protected $order;

	/**
	 * If Slide is draft.
	 *
	 * @var int
	 */
	protected $is_draft = 1;

	public function __clone() {
		unset($this->id, $this->slider_id);
	}

	/**
	 * @return int
	 */
	public function get_id() {
		return (int)$this->id;
	}

	/**
	 * @return int
	 */
	public function get_slider_id() {
		return (int)$this->slider_id;
	}

	/**
	 * @param $slider_id
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function set_slider_id( $slider_id ) {

		if ( ! is_numeric($slider_id) || ! absint($slider_id)  ) {
			throw new Exception( '"slider_id" field must be not negative integer.' );
		}

		$this->slider_id = absint($slider_id);

		return $this;
	}

	/**
	 * @return int
	 */
	public function get_in_new_tab() {
		return (int)$this->in_new_tab;
	}

	/**
	 * @param int $in_new_tab
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function set_in_new_tab( $in_new_tab ) {
		if ( $in_new_tab == 1 || $in_new_tab == 0 ) {
			$this->in_new_tab = (int)$in_new_tab;

			return $this;
		}

		throw new Exception( 'Invalid value for "in_new_tab" field.' );
	}

	/**
	 * Returns the slide type. 'image' for image slide, 'video' for post slide and 'post' for post slide.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * @param string $type
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function set_type( $type ) {

		if ( ! in_array( $type, array( 'image' ) ) ) {
			throw new Exception( 'Invalid value for "type" field.' );
		}

		$this->type = $type;

		return $this;
	}

	/**
	 * @return int
	 */
	public function get_order() {
		return $this->order;
	}

	/**
	 * @param int $order
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function set_order( $order ) {

		$order = absint($order);

		if ( $order < 0 || $order > 99999 ) {
			throw new Exception( '"order" field must be not negative integer.' );
		}

		$this->order = $order;

		return $this;
	}

	/**
	 * @return int
	 */
	public function get_is_draft() {
		return $this->is_draft;
	}

	/**
	 * @param int|bool|null $is_draft
	 *
	 * @return Hugeit_Slider_Slide
	 * @throws Exception
	 */
	public function set_is_draft( $is_draft ) {
		if ( $is_draft == 1 || $is_draft == 0 || $is_draft === NULL ) {
			$this->is_draft = is_numeric($is_draft) ? (int)$is_draft : NULL;

			return $this;
		}

		throw new Exception( 'Invalid value for "$is_draft" field.' );
	}

	/**
	 * Checks if all required fields are set.
	 *
	 * @return bool|array
	 */
	protected function can_be_saved() {
		if ( isset( $this->slider_id, $this->type, $this->order ) ) {
			return true;
		} else {
			$empty_values = array();
			$required_fields = array(
				'slider_id' => $this->slider_id,
//				'attachment_id' => $this->attachment_id,
				'$this' => $this->type,
				'order' => $this->order,
			);

			foreach ( $required_fields as $index => $required_field ) {
				if ( is_null( $required_field ) ) {
					$empty_values[] = $index;
				}
			}

			return $empty_values;
		}
	}

	/**
	 * @param $id_or_type
	 *
	 * @return bool|Hugeit_Slider_Slide_Image
	 */
	public static function get_slide( $id_or_type ) {
		$id = NULL;
		$type = $id_or_type;

		if ( is_numeric( $id_or_type ) && $id_or_type == absint( $id_or_type ) ) {
			$type = $GLOBALS['wpdb']->get_var( "SELECT type FROM " . Hugeit_Slider()->get_slide_table_name() . " WHERE id = " . $id_or_type );
			$id = $id_or_type;
		}

		switch ( strtolower( $type ) ) {
			case 'image' :
				$slide = new Hugeit_Slider_Slide_Image($id);
				$slide->set_type('image');

				return $slide;
			default :
				return false;
		}
	}

	/**
	 * Sets $array[$key] = $value if $value is not NULL.
	 *
	 * @param $key
	 * @param $value
	 * @param $array
	 */
	protected function set_if_not_null( $key, $value, &$array ) {
		if ( $value !== NULL ) {
			$array[ $key ] = $value;
		}
	}

	/**
	 * Deletes slide.
	 *
	 * @param $id
	 *
	 * @return false|int
	 */
	public static function delete( $id ) {
		global $wpdb;

		return $wpdb->query("DELETE FROM " . Hugeit_Slider()->get_slide_table_name() . " WHERE id = " . $id);
	}

	abstract public function save();
}