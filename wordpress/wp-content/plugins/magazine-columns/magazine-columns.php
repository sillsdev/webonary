<?php
/*
Plugin Name: Magazine Columns
Plugin URI: http://bavotasan.com/downloads/magazine-columns-wordpress-plugin/
Description: Divides your post or page content into two or more columns, like a magazine article.
Author: c.bavota
Version: 1.0.7
Author URI: http://www.bavotasan.com/
License: GPL2
*/

/*  Copyright 2015  c.bavota  (email : cbavota@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_filter( 'the_content', 'add_magazine_columns' );
/**
 * Separate the post content into columns.
 *
 * This function is attached to the 'the_content' filter hook.
 *
 * @param	string $content		The post content
 *
 * @return	string Modified to include columns
 */
function add_magazine_columns( $content ) {
	if ( stristr( $content, '<!--column-->' ) && is_singular() ) {
		$col_content = $content;
		$content = '';
		if ( stristr( $col_content, '<!--startcolumns-->' ) ) {
			$topcontent = explode( '<!--startcolumns-->', $col_content );
			$col_content = $topcontent[1];

			if ( stristr( $col_content, '<!--stopcolumns-->' ) ) {
				$bottomcontent = explode( '<!--stopcolumns-->', $col_content );
				$col_content = $bottomcontent[0];
			}
		}

		$col_content = explode( '<!--column-->', $col_content );
		$count = count( $col_content );

		if ( ! empty( $topcontent[0] ) ) {
			$top = explode( '<br />', $topcontent[0] );
			$i = count( $top );
			$top[$i-1] .= '</p>' . "\n";
			$content .= implode( '', $top );
		}

		$content .= '<div id="magazine-columns">';

		foreach( $col_content as $column ) {
			$output = '<div class="column c' . esc_attr( $count ) . '">' . $column . '</div>';
			$output = str_replace( '<div class="column c' . esc_attr( $count ) . '"><br />', '<div class="column c' . esc_attr( $count ) . '"><p>', $output );
			$content .= $output;
		}

		$content .= '</div>';

		if ( ! empty( $bottomcontent[1] ) ) {
			$bottom = explode( '<br />', $bottomcontent[1] );
			$bottom[0] = '<p>' . $bottom[0];
			$content .= implode( '', $bottom );
		}
	}
	return str_replace( '<p></p>', '', $content );
}

add_action( 'wp_head', 'add_magazine_columns_css' );
/**
 * Include the appropriate CSS into the page head.
 *
 * This function is attached to the 'wp_head' action hook.
 *
 * @return	string Echoed CSS
 */
function add_magazine_columns_css() {
	global $post;
	if ( is_singular() ) {
		$content = $post->post_content;
		if ( stristr( $content, '<!--column-->' ) ) {
			?>
<!-- Magazine Columns CSS -->
<style type='text/css'>
#magazine-columns{margin:0 -20px;margin-bottom:1em;overflow:hidden}
.column {float:left;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;box-sizing:border-box;padding:0 20px}
.column.c2{width:50%}
.column.c3{width:33.33%}
.column.c4{width:25%}
.column.c5{width:20%}
.column p:first-child{margin-top:0}
.column p:last-child{margin-bottom:0}
.column img{max-width:100%;height:auto}
</style>
<!-- /Magazine Columns CSS -->
			<?php
		}
	}
}

add_action( 'admin_print_scripts', 'add_magazine_columns_quicktags' );
/**
 * Add quicktags to the post editor.
 *
 * This function is attached to the 'admin_print_scripts' action hook.
 */
function add_magazine_columns_quicktags() {
	wp_enqueue_script( 'my_custom_quicktags', plugins_url( 'magazine-columns/js/mc.js' ), array( 'quicktags' ) );
}