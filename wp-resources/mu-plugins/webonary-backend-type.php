<?php
/**
 * Plugin Name: Webonary Backend Type
 * Description: View and search Webonary backend type in WordPress Multisite
 * Version: 1.0.0
 * Author: SIL International
 * Author URI: http://www.sil.org/
 * License: MIT
 */

! defined( 'ABSPATH' ) and exit;

class webonary_backend_type {
	public static function init() {
		$class = __CLASS__ ;
		if ( empty( $GLOBALS[ $class ] ) ) {
			$GLOBALS[ $class ] = new $class;
		}
	}

	public function __construct() {
		add_filter( 'wpmu_blogs_columns', array( $this, 'add_backend_type' ) );
		add_filter( 'ms_sites_list_table_query_args', array( $this, 'filter_site_search' ) );
		add_action( 'manage_sites_custom_column', array( $this, 'get_backend_type' ), 10, 2 );
	}

	public function add_backend_type( $columns ) {
		$columns['backend_type'] = __('Backend');

		return $columns;
	}

	public function filter_site_search( $args ) {
		if ( isset( $_REQUEST['s'] ) 
		     && in_array( strtolower( $_REQUEST['s'] ), array( 'cloud', 'wordpress' ) ) ) {
			global $wpdb;
			$sql = $wpdb->prepare( "SELECT blog_id FROM {$wpdb->blogmeta} WHERE meta_key=%s AND meta_value=%s", "useCloudBackend", "1" );
			if ( strtolower( $_REQUEST['s'] ) === 'wordpress' ) {
				$sql = "SELECT blog_id FROM {$wpdb->blogs} WHERE blog_id NOT IN ({$sql})";
			}

			$blog_ids = $wpdb->get_col( $sql );
			$args = array_merge( $args, [ 'site__in' => $blog_ids ] );
			unset( $args[ 'search' ] );
		}

		return $args;
	}

	public function get_backend_type( $column_name, $blog_id ) {
		if ( 'backend_type' === $column_name ) {
			if ( get_site_meta($blog_id, 'useCloudBackend', true) ) {
				echo __('Cloud');
			}
			else {
				echo __('Wordpress');
			}
		}

		return $column_name;
	}

}
add_action( 'plugins_loaded', array( 'webonary_backend_type', 'init' ) );