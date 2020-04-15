<?php
/*
Plugin Name: Disable Real MIME Check
Version: 1.0
Plugin URI: https://core.trac.wordpress.org/ticket/39550
Description: Restores the ability to upload non-image files in WordPress 4.7.1 and 4.7.2. Please remove the plugin once WordPress 4.7.3 is available!
Author: Sergey Biryukov
Author URI: http://profiles.wordpress.org/sergeybiryukov/
*/

function wp39550_disable_real_mime_check( $data, $file, $filename, $mimes ) {
	$wp_filetype = wp_check_filetype( $filename, $mimes );

	$ext = $wp_filetype['ext'];
	$type = $wp_filetype['type'];
	$proper_filename = $data['proper_filename'];

	return compact( 'ext', 'type', 'proper_filename' );
}
add_filter( 'wp_check_filetype_and_ext', 'wp39550_disable_real_mime_check', 10, 4 );
?>