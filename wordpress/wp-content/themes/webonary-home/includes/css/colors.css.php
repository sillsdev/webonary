<?php 
add_action('wp_head', 'themezee_css_colors');
function themezee_css_colors() {
	
	$options = get_option('themezee_options');
	
	if ( isset($options['themeZee_color_activate']) and $options['themeZee_color_activate'] == 'true' ) {
		
		echo '<style type="text/css">';
		echo '
			a, a:link, a:visited, .comment-reply-link, #slide_keys a:link, #slide_keys a:visited,
			#topnav li.current_page_item a, #topnavi li.current-menu-item a, #sidebar .widgettitle, #sidebar a:link,
			#sidebar a:visited, .post-title, .post-title a:link, .post-title a:visited, .comment-author .fn {
				color: #'.esc_attr($options['themeZee_colors_full']).';
			}
			.wp-pagenavi .current {
				background: #'.esc_attr($options['themeZee_colors_full']).';
			}
		';
		echo '</style>';
	}
}