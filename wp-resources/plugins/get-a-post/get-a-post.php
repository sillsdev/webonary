<?php
/*
Plugin Name: Get-a-Post
Plugin URI: http://guff.szub.net/get-a-post
Description: Display a specific post (or Page) with standard WP template tags.
Version: R1.4
Author: Kaf Oseo
Author URI: http://szub.net

	Copyright (c) 2004-2006, 2008 Kaf Oseo (http://szub.net)
	Get-a-Post is released under the GNU General Public License, version 2 (GPL2)
	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt

	This is a WordPress plugin (http://wordpress.org).

~Changelog:
R1.4 (Jan-27-2008)
'Rich' release. Adds a 'GETRANDOM' id, which retrieves (surprise) a
random published post. Random rules!

R1.3 (Apr-21-2006)
Bug fix (sorry for the delay). Added a 'GETSTICKY' id, which gets a
post using 'sticky' for a custom field key and '1' for the value.

R1.2 (Mar-03-2006)
Use 'GETPAGE' (all caps) as argument for latest Page, and 'GETPOST'
or no argument for latest post. Tentative suppport for WordPress 2.1.

R1.1 (May-04-2005]
Caches post-meta (custom field) data for use with other plugins or
template tags. Code tweaks. Explicit GPL licensing.

R1 (Mar-01-2005)
"Clementine" release. Handles Pages under WordPress 1.5+. Accepts a
post name (Post/Page slug) or numeric ID as argument.

0.3 (Jan-29-2005)
Intializes post object data to avoid needing to run get_a_post() in
"The Loop" of a template.

0.2 (Jan-28-2005)
Changes for support under WordPress 1.5.
*/

function get_a_post($id='GETPOST') {
	global $post, $tableposts, $tablepostmeta, $wp_version, $wpdb;

	if($wp_version < 1.5)
		$table = $tableposts;
	else
		$table = $wpdb->posts;

	$now = current_time('mysql');
	$name_or_id = '';
	$orderby = 'post_date';

	if( !$id || 'GETPOST' == $id || 'GETRANDOM' == $id ) {
		if( $wp_version < 2.1 )
			$query_suffix = "post_status = 'publish'";
		else
			$query_suffix = "post_type = 'post' AND post_status = 'publish'";
	} elseif('GETPAGE' == $id) {
		if($wp_version < 2.1)
			$query_suffix = "post_status = 'static'";
		else
			$query_suffix = "post_type = 'page' AND post_status = 'publish'";
	} elseif('GETSTICKY' == $id) {
		if($wp_version < 1.5)
			$table .= ', ' . $tablepostmeta;
		else
			$table .= ', ' . $wpdb->postmeta;
		$query_suffix = "ID = post_id AND meta_key = 'sticky' AND meta_value = 1";
	} else {
		$query_suffix = "(post_status = 'publish' OR post_status = 'static')";

		if(is_numeric($id)) {
			$name_or_id = "ID = '$id' AND";
		} else {
			$name_or_id = "post_name = '$id' AND";
		}
	}

	if('GETRANDOM' == $id)
		$orderby = 'RAND()';

	$post = $wpdb->get_row("SELECT * FROM $table WHERE $name_or_id post_date <= '$now' AND $query_suffix ORDER BY $orderby DESC LIMIT 1");
	get_post_custom($post->ID);

	if($wp_version < 1.5)
		start_wp();
	else
		setup_postdata($post);
}
?>