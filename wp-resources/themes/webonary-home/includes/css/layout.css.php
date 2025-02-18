<?php
add_action('wp_head', 'themezee_css_layout');
function themezee_css_layout() {

	$options = get_option('themezee_options');

	if (empty($options))
		return;

	echo '<style>';

	// Rounded Corners?
	if ( $options['themeZee_general_corners'] == 'no' ) {

		echo '
			#wrap, #sidebar .widgettitle, #sidebar ul li ul, #sidebar ul li div, #searchsubmit, .widget-tabnav li a,
			#topnavi, #topnav ul, #navi, .moretext, .arh, .postinfo, .author_box, #frontpage_widgets .widgettitle,
			#frontpage_widgets ul li ul, #frontpage_widgets ul li div, #content-slider, #slide_panel, #comments h3,
			#respond h3, .commentlist .comment, #commentform #submit, .wp-pagenavi .pages, .wp-pagenavi a,
			.wp-pagenavi .current, #bottombar .widgettitle, #bottombar ul .widget, #footer, #image-nav .nav-previous a, #image-nav .nav-next a
			{
				-moz-border-radius: 0;
				-webkit-border-radius: 0;
				-khtml-border-radius: 0;
				border-radius: 0;
			}
		';
	}

	// Add Custom CSS
	if ( $options['themeZee_general_css'] <> '' ) {
		echo $options['themeZee_general_css'];
	}

	echo '</style>';
}
