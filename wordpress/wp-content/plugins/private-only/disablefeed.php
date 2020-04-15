<?php
/*
Plugin Name: Private Only, Disable Feed
Plugin URI: http://www.pixert.com/
Description: This sub plugin disable Feed
Version: 3.5.1
Author: Kate Mag (Pixel Insert)
Author URI: http://www.pixert.com
*/
//disable feed
/* Localization */
load_plugin_textdomain('private-only', false, basename( dirname( __FILE__ ) ) . '/languages' );
function po_disable_feed() {
	 wp_die( printf(__('<strong>Error:</strong> Feed unavailable!','private-only')) );
}
add_action('do_feed', 'po_disable_feed', 1);
add_action('do_feed_rdf', 'po_disable_feed', 1);
add_action('do_feed_rss', 'po_disable_feed', 1);
add_action('do_feed_rss2', 'po_disable_feed', 1);
add_action('do_feed_atom', 'po_disable_feed', 1);
?>
