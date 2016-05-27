<?php
	
	$color_styles = array(
		'brown.css' => __('Brown', ZEE_LANG),
		'darkblue.css' => __('Darkblue', ZEE_LANG),
		'darkgreen.css' => __('Darkgreen', ZEE_LANG),
		'green.css' => __('Green', ZEE_LANG),
		'grey.css' => __('Grey', ZEE_LANG),
		'orange.css' => __('Orange', ZEE_LANG),
		'purple.css' => __('Purple', ZEE_LANG),
		'red.css' => __('Red', ZEE_LANG),
		'standard.css' => __('Standard', ZEE_LANG),
		'custom-color' => __('Custom Color', ZEE_LANG));

	//$default_logo =  get_template_directory_uri() . '/images/logo.png';
	$default_banner = get_template_directory_uri() . '/images/ad_125x125.png';
	
	$sections = array();
	global $sections;
	
	$sections[] = array("id" => "themeZee_main",
				"name" => __('Theme Settings', ZEE_LANG));

	$settings = array();
	global $settings;
	
	
### MAIN SETTINGS
#######################################################################################
  /*
	$settings[] = array("name" => "Mode",
					"desc" => "",
					"id" => "themeZee_blog_mode",
					"std" => "0",
					"type" => "radio",
					'choices' => array(
								0 => 'Dictionary',
								1 => 'Blog'),
					"section" => "themeZee_main");
	
	$settings[] = array("name" => __('Meta Description', ZEE_LANG),
			"desc" => __('Enter the description displayed by search engines.', ZEE_LANG),
			"id" => "themeZee_description",
			"std" => "",
			"type" => "textarea",
			"section" => "themeZee_main");
	
	$settings[] = array("name" => __('Meta Keywords', ZEE_LANG),
			"desc" => __('Enter some keywords for better indexing.', ZEE_LANG),
			"id" => "themeZee_keywords",
			"std" => "",
			"type" => "textarea",
			"section" => "themeZee_main");
	
	*/
	$settings[] = array("name" => __('Footer Content', ZEE_LANG),
					"desc" => __('Enter here the content which is displayed in the footer.', ZEE_LANG),
					"id" => "themeZee_footer",
					"std" => "Place your Footer Content here",
					"type" => "textarea",
					"section" => "themeZee_main");

	/*
	$settings[] = array("name" => __('Right Footer Content', ZEE_LANG),
					"desc" => __('Enter here the content which is displayed in the right side of the footer.', ZEE_LANG),
					"id" => "themeZee_footer_right",
					"std" => "Place your Footer Content here",
					"type" => "textarea",
					"section" => "themeZee_main");
	*/
	/*
	$settings[] = array("name" => "Theme Style",
					"desc" => __('Please select your color scheme here.', ZEE_LANG),
					"id" => "themeZee_stylesheet",
					"std" => "standard.css",
					"type" => "select",
					'choices' => $color_styles,
					"section" => "themeZee_main"
					);
	
	$settings[] = array("name" => __('Custom Color', ZEE_LANG),
					"desc" => __("Select a custom color here (You have to select the 'custom color' option above).", ZEE_LANG),
					"id" => "themeZee_color",
					"std" => "000000",
					"type" => "colorpicker",
					"section" => "themeZee_main");
	*/
	
	$customcss = "hideCustomCSS";
	if($_GET['customcss'] == 1)
	{
		$customcss = "showCustomCSS";
	}
	$settings[] = array("name" => __('Custom CSS', ZEE_LANG),
					"desc" => __('Insert your own custom css code into the head of the theme.', ZEE_LANG),
					"id" => "themeZee_custom_css",
					"std" => "",
					"type" => $customcss,
					"section" => "themeZee_main");
	$settings[] = array("name" => "",
					"desc" => __('Paste the full Image URL of your logo.', ZEE_LANG),
					"id" => "themeZee_logo",
					"std" => $default_logo,
					"type" => "logo",
					"section" => "themeZee_main");
	
?>