<?php

// Load additional Predefined Color Schemes if Custom Colors is deactivated
function themezee_load_custom_css() {
	$options = get_option('themezee_options');
	
	// Load PredefinedColor CSS
	if ( !isset($options['themeZee_color_activate']) or $options['themeZee_color_activate'] != 'true' ) {
		$cssfile = $options['themeZee_stylesheet'] <> '' ? $options['themeZee_stylesheet'] : 'standard.css';
		$stylesheet = get_template_directory_uri() . '/includes/css/colorschemes/' . $cssfile;
		wp_register_style('zeeDisplay_colorscheme', $stylesheet, array('zeeDisplay_stylesheet'));
		wp_enqueue_style( 'zeeDisplay_colorscheme');
	}
}
add_action('wp_enqueue_scripts', 'themezee_load_custom_css');


// Include Fonts from Google Web Fonts API
function themezee_load_web_fonts() {
	wp_register_style('themezee_default_font', 'https://fonts.googleapis.com/css?family=Share');
	wp_enqueue_style('themezee_default_font');
	wp_register_style('themezee_default_font_two', 'https://fonts.googleapis.com/css?family=Carme');
	wp_enqueue_style('themezee_default_font_two');
}
add_action('wp_enqueue_scripts', 'themezee_load_web_fonts');

// Include CSS Files
locate_template('/includes/css/colors.css.php', true);
locate_template('/includes/css/layout.css.php', true);

?>