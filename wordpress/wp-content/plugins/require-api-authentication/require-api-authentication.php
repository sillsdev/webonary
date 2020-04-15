<?php
/*
 Plugin Name: Require API Authentication
 Description: Only authenticated users can access the API
 Version: 1.0
 Author: Philip Perry
 License: GPLv3
 */


add_filter( 'rest_authentication_errors', function( $result ) {
	global $wp;
	$current_slug = add_query_arg( array(), $wp->request );
	if ( ! empty($result) || $current_slug == "wp-json/webonary/import") {
		return $result;
	}
	if ( ! is_user_logged_in()) {
		return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array( 'status' => 401 ) );
	}
	return $result;
});