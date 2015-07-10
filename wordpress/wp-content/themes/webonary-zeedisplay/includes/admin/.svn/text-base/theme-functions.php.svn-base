<?php

add_action('wp_print_styles', 'theme_color_style');
function theme_color_style() { 
	$options = get_option('themezee_options');
	
	if ( $options['themeZee_stylesheet'] != '' and $options['themeZee_stylesheet'] <> 'custom-color' ) {
		$stylesheet = get_template_directory_uri() . '/includes/styles/' . $options['themeZee_stylesheet'];
		wp_register_style('zee_color_style', $stylesheet, array('zee_stylesheet'));
		wp_enqueue_style( 'zee_color_style');
	}
}

add_action('admin_init', 'theme_admin_head');
function theme_admin_head() { 

	wp_register_style('zee_admin_css', get_template_directory_uri() .'/includes/admin/admin-style.css');
    wp_enqueue_style( 'zee_admin_css');
	
	wp_register_style('zee_colorpicker_css', get_template_directory_uri().'/includes/admin/colorpicker/colorpicker.css');
    wp_enqueue_style( 'zee_colorpicker_css');
	
	wp_register_script('zee_colorpicker_js', get_template_directory_uri() .'/includes/admin/colorpicker/colorpicker.js', false);
	wp_enqueue_script('zee_colorpicker_js');
	
	wp_register_script('zee_eye', get_template_directory_uri() .'/includes/admin/colorpicker/eye.js', array('zee_colorpicker_js'));
	wp_enqueue_script('zee_eye');
	
	wp_register_script('zee_utils', get_template_directory_uri() .'/includes/admin/colorpicker/utils.js', array('zee_eye'));
	wp_enqueue_script('zee_utils');
	
	wp_register_script('zee_mycolorpicker', get_template_directory_uri() .'/includes/admin/colorpicker/mycolorpicker.js', array('zee_utils'));
	wp_enqueue_script('zee_mycolorpicker');
}
?>