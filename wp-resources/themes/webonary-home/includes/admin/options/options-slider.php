<?php
	
	function themezee_get_slider_sections() {
		$themezee_sections = array();
		
		$themezee_sections[] = array("id" => "themeZee_slider",
					"name" => __('Featured Posts Slider', 'themezee_lang'));
					
		return $themezee_sections;
	}
	
	function themezee_get_slider_settings() {
		
		$categories = array();
		$categories[''] = 'All Categories';
		$cats = get_categories(); 
		foreach ($cats as $cat) {
			$categories[$cat->category_nicename] = $cat->cat_name;
		}
		
		$themezee_settings = array();
						
		### POST SLIDER SETTINGS
		#######################################################################################
		$themezee_settings[] = array("name" => __('Show Post Slider?', 'themezee_lang'),
						"desc" => __('Check this if you want to show the Featured Post Slider.', 'themezee_lang'),
						"id" => "themeZee_show_slider",
						"std" => "false",
						"type" => "checkbox",
						"section" => "themeZee_slider");
						
		$themezee_settings[] = array("name" => __('Slider Title', 'themezee_lang'),
						"desc" => __('Enter here your headline which is displayed above the featured posts.', 'themezee_lang'),
						"id" => "themeZee_slider_title",
						"std" => "Featured Posts",
						"type" => "text",
						"section" => "themeZee_slider");
						
		$themezee_settings[] = array("name" => "Slider Effect",
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
						
		$themezee_settings[] = array("name" => __(' Slider Content', 'themezee_lang'),
						"desc" => "",
						"id" => "themeZee_slider_content",
						"std" => "recent",
						"type" => "radio",
						'choices' => array(
									'recent' => __('Show recent posts', 'themezee_lang'),
									'popular' => __('Show popular posts', 'themezee_lang')),
						"section" => "themeZee_slider");
						
		$themezee_settings[] = array("name" => __('Slider Category', 'themezee_lang'),
						"desc" => __("Select a category which posts are displayed at the featured posts slider .", 'themezee_lang'),
						"id" => "themeZee_slider_category",
						"std" => "",
						"type" => "select",
						'choices' => $categories,
						"section" => "themeZee_slider");

		$themezee_settings[] = array("name" => __('Number of Posts', 'themezee_lang'),
						"desc" => __('Enter the number how much posts should be displayed in the post slider.', 'themezee_lang'),
						"id" => "themeZee_slider_limit",
						"std" => "5",
						"type" => "text",
						"section" => "themeZee_slider");
		
		return $themezee_settings;
	}

?>