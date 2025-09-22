<?php 
add_action('wp_head', 'themezee_include_custom_css');
function themezee_include_custom_css() {
	
	echo '<style type="text/css">';
	$options = get_option('themezee_options');
	if ( $options['themeZee_stylesheet'] == "custom-color" ) {
		echo '
		a, a:link, a:visited {
			color: #'.esc_attr($options['themeZee_color']).';
		}
		#sidebar ul li h2,
		#topnavi ul li.current_page_item a, #topnavi ul li.current-cat a, #topnavi ul li.current-menu-item a  {
			color: #'.esc_attr($options['themeZee_color']).' !important;
		}
		.post h2, .attachment h2, .post h2 a:link, .post h2 a:visited {
			color: #'.esc_attr($options['themeZee_color']).';
		}
		#slideshow .post h2 a{  
			color: #'.esc_attr($options['themeZee_color']).';
		}
		.comment-author .fn, .comment-reply-link {
			color: #'.esc_attr($options['themeZee_color']).' !important;
		}
		.wp-pagenavi .current {
			background-color: #'.esc_attr($options['themeZee_color']).';
		}
		#slide_keys a:link, #slide_keys a:visited {
			color: #'.esc_attr($options['themeZee_color']).' !important;
		}
		';
		}
	if ( $options['themeZee_custom_css'] <> "" ) { echo esc_attr($options['themeZee_custom_css']); }
	echo '</style>';
}
