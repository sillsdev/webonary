<?php
	
	function themezee_get_general_sections() {
		$themezee_sections = array();
		
		$themezee_sections[] = array("id" => "themeZee_general_layout",
					"name" => __('Layout Settings', 'themezee_lang'));
					
		return $themezee_sections;
	}
	
	function themezee_get_general_settings() {

		$themezee_settings = array();
	
		### GENERAL SETTINGS
		#######################################################################################
		$themezee_settings[] = array("name" => "Logo",
						"desc" => __('Paste the full Image URL of your logo.', 'themezee_lang'),
						"id" => "themeZee_general_logo",
						"std" => "",
						"type" => "image",
						"section" => "themeZee_general_layout");
						
		$themezee_settings[] = array("name" => __('Rounded Corners', 'themezee_lang'),
						"desc" => "",
						"id" => "themeZee_general_corners",
						"std" => 'yes',
						"type" => "radio",
						'choices' => array(
									'yes' => __('Yes, use rounded corners.', 'themezee_lang'),
									'no' => __('Dont use rounded corners.', 'themezee_lang')),
						"section" => "themeZee_general_layout"
						);

		$themezee_settings[] = array("name" => __('Footer Content', 'themezee_lang'),
						"desc" => __('Enter here the content which is displayed in the footer.', 'themezee_lang'),
						"id" => "themeZee_general_footer",
						"std" => "Place your Footer Content here",
						"type" => "html",
						"section" => "themeZee_general_layout");
						
		$themezee_settings[] = array("name" => __('Custom CSS', 'themezee_lang'),
						"desc" => __('Place your Custom CSS code here.', 'themezee_lang'),
						"id" => "themeZee_general_css",
						"std" => "",
						"type" => "textarea",
						"section" => "themeZee_general_layout");
						
		return $themezee_settings;
	}

?>