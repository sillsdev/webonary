<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

final class Hugeit_Slider_Slide_Image extends Hugeit_Slider_Slide implements Hugeit_Slider_Slide_Image_Interface {

	/**
	 * Slide title.
	 *
	 * @var string
	 */
	private $title;

	/**
	 * Slide description.
	 *
	 * @var string
	 */
	private $description;

	/**
	 * Slide URL.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Slide image attachment id.
	 *
	 * @var int
	 */
	private $attachment_id;

	/**
	 * Hugeit_Slider_Slide_Image constructor.
	 *
	 * @param null|int $id
	 */
	public function __construct( $id = NULL ) {
		$this->type = 'image';

		if ( is_numeric($id) && absint( $id ) == $id ) {
			global $wpdb;

			$slide = $wpdb->get_row( "SELECT * FROM " . Hugeit_Slider()->get_slide_table_name() . " WHERE id = " . $id, ARRAY_A );

			if ( ! is_null( $slide ) ) {
				$this->id = $id;

				foreach ( $slide as $slide_option_name => $slide_option_value ) {

					str_replace(array('video_', 'post_'), '', $slide_option_name);

					$function_name = 'set_' . $slide_option_name;

					if ( method_exists( $this, $function_name ) ) {
						call_user_func( array( $this, $function_name ), $slide_option_value );
					}
				}
			}
		}
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return is_admin() ? htmlentities($this->title) : $this->title;
	}

	/**
	 * @param string $title
	 *
	 * @return Hugeit_Slider_Slide_Image
	 * @throws Exception
	 */
	public function set_title( $title ) {

		$title = wp_kses_post( $title );
		$title = wp_unslash($title);

		if ( strlen( $title ) <= 512 ) {
			$this->title = $title;

			return $this;
		}

		throw new Exception( 'Invalid value for "title" field.' );
	}

	/**
	 * @return string
	 */
	public function get_description() {
		return is_admin() ? htmlentities($this->description) : $this->description;
	}

	/**
	 * @param string $description
	 *
	 * @return Hugeit_Slider_Slide_Image
	 * @throws Exception
	 */
	public function set_description( $description ) {

		$description = wp_kses_post( $description );
		$description = wp_unslash( $description );

		if ( strlen( $description ) <= 2048 ) {
			$this->description = $description;

			return $this;
		}

		throw new Exception( 'Invalid value for "description" field.' );
	}

	/**
	 * @return string
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * @param string $url
	 *
	 * @return Hugeit_Slider_Slide_Image
	 * @throws Exception
	 */
	public function set_url( $url ) {
		$url = esc_url($url);

		if (strlen($url) <= 2048) {
			$this->url = $url;

			return $this;
		}

		throw new Exception('Invalid value for "url" field.');
	}

	/**
	 * @return int
	 */
	public function get_attachment_id() {
		return (int)$this->attachment_id;
	}

	/**
	 * @param int $attachment_id
	 *
	 * @return $this
	 */
	public function set_attachment_id( $attachment_id ) {

		$this->attachment_id = absint($attachment_id);

		return $this;
	}

	private function can_ba_saved() {
		return $this->attachment_id === NULL ? array('attachment_id') : true;
	}

	public function save() {
		$can_be_saved_self = $this->can_be_saved();
		$can_be_saved_parent = parent::can_be_saved();

		$can_be_saved = $can_be_saved_self === true && $can_be_saved_parent === true;

		if ( ! $can_be_saved ) {
			$exception_text = '';

			if (is_array($can_be_saved_self)) {
				$exception_text .= implode(', ', $can_be_saved_self);
			}

			if (is_array($can_be_saved_parent)) {
				$exception_text = $exception_text === '' ? implode(', ', $can_be_saved_parent) : $exception_text . ', ' . implode(', ', $can_be_saved_parent);
			}

			throw new Exception($exception_text . ' fields are required.');
		}

		global $wpdb;

		$slide_data = array();

		$this->set_if_not_null('slider_id', $this->slider_id, $slide_data);
		$this->set_if_not_null('title', $this->title, $slide_data);
		$this->set_if_not_null('description', $this->description, $slide_data);
		$this->set_if_not_null('url', $this->url, $slide_data);
		$this->set_if_not_null('attachment_id', $this->attachment_id, $slide_data);
		$this->set_if_not_null('in_new_tab', $this->in_new_tab, $slide_data);
		$this->set_if_not_null('type', $this->type, $slide_data);
		$this->set_if_not_null('order', $this->order, $slide_data);
//		$this->set_if_not_null('draft', $this->is_draft, $slide_data);
		$slide_data['draft'] = $this->is_draft;

		$success = is_null($this->id)
			? $wpdb->insert(Hugeit_Slider()->get_slide_table_name(), $slide_data)
			: $wpdb->update(Hugeit_Slider()->get_slide_table_name(), $slide_data, array('id' => $this->id));

		if ($success !== false && !isset($this->id)) {
			$this->id = $wpdb->insert_id;
			return $wpdb->insert_id;
		} elseif ($success !== false && isset($this->id)) {
			return $this->id;
		} else {
			return false;
		}
	}
}