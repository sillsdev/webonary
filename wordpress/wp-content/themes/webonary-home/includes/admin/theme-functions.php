<?php
	
	// Define Theme URL
	define('ZEE_THEME_URL', 'http://themezee.com/zeedisplay/');
	
	// Define all Settings Pages Tabs
	function themezee_get_settings_page_tabs() {
		$tabs = array(
			'welcome' => __('Welcome', 'themezee_lang'),
			'general' => __('General', 'themezee_lang'),
			'colors' => __('Colors', 'themezee_lang'),
			'slider' => __('Post Slider', 'themezee_lang'),
			'social' => __('Social Buttons', 'themezee_lang'),
			'ads' => __('Ads', 'themezee_lang')
		);
		return $tabs;
	}
	
	function themezee_get_sections($tab) {
			
		// Get Section
		switch ( $tab ) :
			case 'general' :
				locate_template('/includes/admin/options/options-general.php', true);
				$themezee_sections = themezee_get_general_sections();
			break;
			case 'colors' :
				locate_template('/includes/admin/options/options-colors.php', true);
				$themezee_sections = themezee_get_colors_sections();
			break;
			case 'slider' :
				locate_template('/includes/admin/options/options-slider.php', true);
				$themezee_sections = themezee_get_slider_sections();
			break;
			case 'social' :
				locate_template('/includes/admin/options/options-social.php', true);
				$themezee_sections = themezee_get_social_sections();
			break;
			case 'ads' :
				locate_template('/includes/admin/options/options-ads.php', true);
				$themezee_sections = themezee_get_ads_sections();
			break;
			default :
				locate_template('/includes/admin/options/options-general.php', true);
				$themezee_sections = themezee_get_general_sections();
			break;
		endswitch;
		
		return $themezee_sections;
	}
	
	function themezee_get_settings($tab = 'general') {
	
		// Get Section
		switch ( $tab ) :
			case 'general' :
				locate_template('/includes/admin/options/options-general.php', true);
				$themezee_settings = themezee_get_general_settings();
			break;
			case 'colors' :
				locate_template('/includes/admin/options/options-colors.php', true);
				$themezee_settings = themezee_get_colors_settings();
			break;
			case 'slider' :
				locate_template('/includes/admin/options/options-slider.php', true);
				$themezee_settings = themezee_get_slider_settings();
			break;
			case 'social' :
				locate_template('/includes/admin/options/options-social.php', true);
				$themezee_settings = themezee_get_social_settings();
			break;
			case 'ads' :
				locate_template('/includes/admin/options/options-ads.php', true);
				$themezee_settings = themezee_get_ads_settings();
			break;
			default :
				locate_template('/includes/admin/options/options-general.php', true);
				$themezee_settings = themezee_get_general_settings();
			break;
		 endswitch;
		
		return $themezee_settings;
	}

	// Add Scripts and CSS for ThemeZee Options Panel	
	add_action('admin_enqueue_scripts', 'themezee_admin_head');
	function themezee_admin_head() { 
		if ( isset($_GET['page']) and $_GET['page'] == 'themezee' ) :
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
			
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
			
			wp_register_script('zee_image_upload', get_template_directory_uri() .'/includes/admin/jquery-image-upload.js', array('jquery','media-upload','thickbox'));
			wp_localize_script('zee_image_upload', 'zee_localizing_upload_js', array(
				'use_this_image' => __('Use this Image', 'themezee_lang')
			));
			
			wp_enqueue_script('zee_image_upload');
			wp_enqueue_style('thickbox');
		endif;
	}
	
	
?>