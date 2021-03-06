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

	$default_logo =  get_template_directory_uri() . '/images/logo.png';
	$default_banner = get_template_directory_uri() . '/images/ad_125x125.png';
	
	$sections = array();
	global $sections;
	
	$sections[] = array("id" => "themeZee_main",
				"name" => __('Theme Settings', ZEE_LANG));
				
	$sections[] = array("id" => "themeZee_slider",
				"name" => __('Featured Post Slider', ZEE_LANG));
				
	$sections[] = array("id" => "themeZee_buttons",
				"name" => __('Social Media Buttons', ZEE_LANG));
				
	$sections[] = array("id" => "themeZee_banner",
				"name" => __('125x125 Ad Spots', ZEE_LANG));

	$settings = array();
	global $settings;
	
	
### MAIN SETTINGS
#######################################################################################
	$settings[] = array("name" => "Mode",
					"desc" => "",
					"id" => "themeZee_blog_mode",
					"std" => "0",
					"type" => "radio",
					'choices' => array(
								0 => 'Dictionary',
								1 => 'Blog'),
					"section" => "themeZee_main");

	$settings[] = array("name" => "Logo",
					"desc" => __('Paste the full Image URL of your logo.', ZEE_LANG),
					"id" => "themeZee_logo",
					"std" => $default_logo,
					"type" => "logo",
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
	
							
	$settings[] = array("name" => __('Footer Content', ZEE_LANG),
					"desc" => __('Enter here the content which is displayed in the footer.', ZEE_LANG),
					"id" => "themeZee_footer",
					"std" => "Place your Footer Content here",
					"type" => "textarea",
					"section" => "themeZee_main");
						
	$settings[] = array("name" => __('Right Footer Content', ZEE_LANG),
					"desc" => __('Enter here the content which is displayed in the right side of the footer.', ZEE_LANG),
					"id" => "themeZee_footer_right",
					"std" => "Place your Footer Content here",
					"type" => "textarea",
					"section" => "themeZee_main");

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

	$settings[] = array("name" => __('Custom CSS', ZEE_LANG),
					"desc" => __('Insert your own custom css code into the head of the theme.', ZEE_LANG),
					"id" => "themeZee_custom_css",
					"std" => "",
					"type" => "textarea",
					"section" => "themeZee_main");
					
### POST SLIDER SETTINGS
#######################################################################################
	$settings[] = array("name" => __('Show Post Slider?', ZEE_LANG),
					"desc" => __('Check this if you want to show the Featured Post Slider.', ZEE_LANG),
					"id" => "themeZee_show_slider",
					"std" => "false",
					"type" => "checkbox",
					"section" => "themeZee_slider");
					
	$settings[] = array("name" => __('Slider Title', ZEE_LANG),
					"desc" => __('Enter here your headline which is displayed above the featured posts.', ZEE_LANG),
					"id" => "themeZee_slider_title",
					"std" => "Featured Posts",
					"type" => "text",
					"section" => "themeZee_slider");
					
	$settings[] = array("name" => "Slider Effect",
					"desc" => "",
					"id" => "themeZee_slider_mode",
					"std" => "0",
					"type" => "radio",
					'choices' => array(
								0 => 'Horizontal Slider',
								1 => 'DropDown Slider',
								2 => 'Fade Slider'),
					"section" => "themeZee_slider"
					);

	$settings[] = array("name" => __('Slider Content', ZEE_LANG),
					"desc" => "",
					"id" => "themeZee_slider_content",
					"std" => "0",
					"type" => "radio",
					'choices' => array(
								0 => __('Show latest posts', ZEE_LANG),
								1 => __('Show latest posts from category "featured"', ZEE_LANG),
								2 => __('Show latest posts with post_meta_key "featured"', ZEE_LANG),
								3 => __('Show latest posts from custom category(enter ID below)', ZEE_LANG)),
					"section" => "themeZee_slider"
					);
					
	$settings[] = array("name" => __('category ID', ZEE_LANG),
					"desc" => __("Please enter the category ID you'd like to include in the slideshow.(You have to tick the last option above)", ZEE_LANG),
					"id" => "themeZee_slider_cat",
					"std" => "1",
					"type" => "text",
					"section" => "themeZee_slider");

	$settings[] = array("name" => __('Number of Posts', ZEE_LANG),
					"desc" => __('Enter the number how much posts should be displayed in the post slider.', ZEE_LANG),
					"id" => "themeZee_slider_limit",
					"std" => "5",
					"type" => "text",
					"section" => "themeZee_slider");
	
### SOCIALMEDIA BUTTONS SETTINGS
#######################################################################################

	$settings[] = array("name" => "RSS URL",
					"desc" => __('Enter your RSS URL (e.g. Feedburner Feed) here.', ZEE_LANG),
					"id" => "themeZee_rss",
					"std" => "",
					"type" => "text",
					"section" => "themeZee_buttons");
					
	$settings[] = array("name" => "Email",
					"desc" => __('Enter your Email URL (e.g. Feedburner Email Subscription) here.', ZEE_LANG),
					"id" => "themeZee_email",
					"std" => "",
					"type" => "text",
					"section" => "themeZee_buttons");
					
	$settings[] = array("name" => "Twitter",
					"desc" => __('Enter the URL to your Twitter Profile here.', ZEE_LANG),
					"id" => "themeZee_twitter",
					"std" => "",
					"type" => "text",
					"section" => "themeZee_buttons");
					
	$settings[] = array("name" => "Facebook",
					"desc" => __('Enter the URL to your Facebook Profile here.', ZEE_LANG),
					"id" => "themeZee_facebook",
					"std" => "",
					"type" => "text",
					"section" => "themeZee_buttons");
					
	$settings[] = array("name" => "Tumblr",
					"desc" => __('Enter the URL to your Tumblr Blog here.', ZEE_LANG),
					"id" => "themeZee_tumblr",
					"std" => "",
					"type" => "text",
					"section" => "themeZee_buttons");	
					
	$settings[] = array("name" => "LinkedIn",
					"desc" => __('Enter the URL to your LinkedIn Profile here.', ZEE_LANG),
					"id" => "themeZee_linkedin",
					"std" => "",
					"type" => "text",
					"section" => "themeZee_buttons");	
					
	$settings[] = array("name" => "Xing",
					"desc" => __('Enter the URL to your Xing Profile here.', ZEE_LANG),
					"id" => "themeZee_xing",
					"std" => "",
					"type" => "text",
					"section" => "themeZee_buttons");	
					
	$settings[] = array("name" => "Delicious",
					"desc" => __('Enter the URL to your Delicious Profile here.', ZEE_LANG),
					"id" => "themeZee_delicious",
					"std" => "",
					"type" => "text",
					"section" => "themeZee_buttons");
					
	$settings[] = array("name" => "Digg",
					"desc" => __('Enter the URL to your Digg Profile here.', ZEE_LANG),
					"id" => "themeZee_digg",
					"std" => "",
					"type" => "text",
					"section" => "themeZee_buttons");
					
	$settings[] = array("name" => "Flickr",
					"desc" => __('Enter the URL to your Flickr Profile here.', ZEE_LANG),
					"id" => "themeZee_flickr",
					"std" => "",
					"type" => "text",
					"section" => "themeZee_buttons");	
					
	$settings[] = array("name" => "Youtube",
					"desc" => __('Enter the URL to your Youtube Profile here.', ZEE_LANG),
					"id" => "themeZee_youtube",
					"std" => "",
					"type" => "text",
					"section" => "themeZee_buttons");
					
	$settings[] = array("name" => "Vimeo",
					"desc" => __('Enter the URL to your Vimeo Profile here.', ZEE_LANG),
					"id" => "themeZee_vimeo",
					"std" => "",
					"type" => "text",
					"section" => "themeZee_buttons");
					
### 125x125 Banner SETTINGS
#######################################################################################	
	
	$settings[] = array("name" => __('Rotate banners?', ZEE_LANG),
					"desc" => __('Check this to randomly rotate the ad spots.', ZEE_LANG),
					"id" => "themeZee_rotate",
					"std" => "false",
					"type" => "checkbox",
					"section" => "themeZee_banner");	

	$settings[] = array("name" => __('Ad Spot Image URL', ZEE_LANG) . ' #1',
					"desc" => __('Enter the image URL for this ad spot.', ZEE_LANG),
					"id" => "themeZee_ad_image_1",
					"std" => $default_banner,
					"type" => "text",
					"section" => "themeZee_banner");
						
	$settings[] = array("name" =>  __('Ad Spot Destination', ZEE_LANG) . ' #1',
					"desc" => __('Enter the URL where this ad spot points to.', ZEE_LANG),
					"id" => "themeZee_ad_url_1",
					"std" => "",
					"type" => "text",
					"section" => "themeZee_banner");

	$settings[] = array("name" => __('Ad Spot Image URL', ZEE_LANG) . ' #2',
					"desc" => "",
					"id" => "themeZee_ad_image_2",
					"std" => $default_banner,
					"type" => "text",
					"section" => "themeZee_banner");
						
	$settings[] = array("name" => __('Ad Spot Destination', ZEE_LANG) . ' #2',
					"desc" => "",
					"id" => "themeZee_ad_url_2",
					"std" => "",
					"type" => "text",
					"section" => "themeZee_banner");

	$settings[] = array("name" => __('Ad Spot Image URL', ZEE_LANG) . ' #3',
					"desc" => "",
					"id" => "themeZee_ad_image_3",
					"std" => $default_banner,
					"type" => "text",
					"section" => "themeZee_banner");
						
	$settings[] = array("name" => __('Ad Spot Destination', ZEE_LANG) . ' #3',
					"desc" => "",
					"id" => "themeZee_ad_url_3",
					"std" => "",
					"type" => "text",
					"section" => "themeZee_banner");

	$settings[] = array("name" => __('Ad Spot Image URL', ZEE_LANG) . ' #4',
					"desc" => "",
					"id" => "themeZee_ad_image_4",
					"std" => $default_banner,
					"type" => "text",
					"section" => "themeZee_banner");
						
	$settings[] = array("name" => __('Ad Spot Destination', ZEE_LANG) . ' #4',
					"desc" => "",
					"id" => "themeZee_ad_url_4",
					"std" => "",
					"type" => "text",
					"section" => "themeZee_banner");

	$settings[] = array("name" => __('Ad Spot Image URL', ZEE_LANG) . ' #5',
					"desc" => "",
					"id" => "themeZee_ad_image_5",
					"std" => "",
					"type" => "text",
					"section" => "themeZee_banner");
						
	$settings[] = array("name" => __('Ad Spot Destination', ZEE_LANG) . ' #5',
					"desc" => "",
					"id" => "themeZee_ad_url_5",
					"std" => "",
					"type" => "text",
					"section" => "themeZee_banner");

	$settings[] = array("name" => __('Ad Spot Image URL', ZEE_LANG) . ' #6',
					"desc" => "",
					"id" => "themeZee_ad_image_6",
					"std" => "",
					"type" => "text",
					"section" => "themeZee_banner");
						
	$settings[] = array("name" => __('Ad Spot Destination', ZEE_LANG) . ' #6',
					"desc" => "",
					"id" => "themeZee_ad_url_6",
					"std" => "",
					"type" => "text",
					"section" => "themeZee_banner");

?>