<?php
// NOTE: make sure to first run: update webonary.wp_blogs set domain = 'www.webonary.work' where domain = 'www.webonary.org;

global $wpdb;
chdir( ABSPATH );

$oldDomain = 'webonary.org';
$newDomain = 'webonary.localhost';

echo_msg( 'Starting...' );

//activate_plugin('enable-media-replace/enable-media-replace.php');

$search_replace_options = "--recurse-objects --skip-columns=guid --skip-tables='wp_users' --report-changed-only";

$sql = $wpdb->prepare( "UPDATE wp_blogs SET domain = replace(domain, %s, %s)", "www.$oldDomain", $newDomain );
echo_msg( $sql );
echo_msg( "Result: " . $wpdb->query( $sql ) );

$sql = $wpdb->prepare( "UPDATE wp_options SET option_value = replace(option_value, %s, %s)", "https://www.$oldDomain", "http://$newDomain" );
echo_msg( $sql );
echo_msg( "Result: " . $wpdb->query( $sql ) );

$sql = $wpdb->prepare( "UPDATE wp_options SET option_value = replace(option_value, %s, %s)", "https://$oldDomain", "http://$newDomain" );
echo_msg( $sql );
echo_msg( "Result: " . $wpdb->query( $sql ) );

$sql = $wpdb->prepare( "UPDATE wp_options SET option_value = replace(option_value, %s, %s)", "http://www.$oldDomain", "http://$newDomain" );
echo_msg( $sql );
echo_msg( "Result: " . $wpdb->query( $sql ) );

$sql = $wpdb->prepare( "UPDATE wp_options SET option_value = replace(option_value, %s, %s)", "http://$oldDomain", "http://$newDomain" );
echo_msg( $sql );
echo_msg( "Result: " . $wpdb->query( $sql ) );


$result = $wpdb->get_results( "SELECT * FROM $wpdb->blogs" );
foreach ( $result as $blog ) {
	$id = $blog->blog_id;
	// Replace subdomain to subdirectory in all links tables
	echo_msg( "Changing " . $blog->domain );
	exec_wp_cli( "search-replace --url='https://$blog->domain$blog->path' 'https://www.$oldDomain' 'http://$newDomain' $search_replace_options" );
	exec_wp_cli( "search-replace --url='https://$blog->domain$blog->path' 'https://$oldDomain' 'http://$newDomain' $search_replace_options" );

	// Fix map file
	fix_map_file( $oldDomain, $newDomain );

	// Fix reverse entries
	$sql = $wpdb->prepare( "UPDATE wp_{$id}_sil_reversals SET reversal_content=REPLACE(reversal_content, %s, %s)", "https://www.$oldDomain", "http://$newDomain" );
	echo_msg( $sql );
	echo_msg( "Result: " . $wpdb->query( $sql ) );

	$sql = $wpdb->prepare( "UPDATE wp_{$id}_sil_reversals SET reversal_content=REPLACE(reversal_content, %s, %s)", "https://$oldDomain", "http://$newDomain" );
	echo_msg( $sql );
	echo_msg( "Result: " . $wpdb->query( $sql ) );

	$sql = $wpdb->prepare( "UPDATE wp_{$id}_sil_reversals SET reversal_content=REPLACE(reversal_content, %s, %s)", "www.$oldDomain", $newDomain );
	echo_msg( $sql );
	echo_msg( "Result: " . $wpdb->query( $sql ) );

	$sql = $wpdb->prepare( "UPDATE wp_{$id}_sil_reversals SET reversal_content=REPLACE(reversal_content, %s, %s)", $oldDomain, $newDomain );
	echo_msg( $sql );
	echo_msg( "Result: " . $wpdb->query( $sql ) );
}

echo_msg( 'All done!' );
exit;

function fix_map_file( $oldDomain, $newDomain )
{
	echo_msg( "Fixing strings in map file $oldDomain to $newDomain" );;
	$map_file = 'wp-content/uploads/webonary-sites.gpx';
	$contents = file_get_contents( $map_file );
	$contents = str_replace( $oldDomain, $newDomain, $contents );
	file_put_contents( $map_file, $contents );
}

function exec_wp_cli( $cmd )
{
	// NB: it's no longer necessary to use sudo
	// $cmd = "sudo wp $cmd --allow-root";
	$cmd = "wp $cmd --allow-root";
	echo_msg( $cmd );;
	echo shell_exec( "$cmd 2>&1" );
}

function echo_msg( $msg )
{
	echo current_time( 'mysql' ) . " $msg\n";
}
