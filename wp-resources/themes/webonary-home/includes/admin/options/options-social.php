<?php
	
	function themezee_get_social_sections() {
		$themezee_sections = array();

		$themezee_sections[] = array("id" => "themeZee_buttons",
					"name" => __('Social Media Buttons', 'themezee_lang'));
					
		return $themezee_sections;
	}
	
	function themezee_get_social_settings() {
		
		$themezee_settings = array();
		
		### SOCIALMEDIA BUTTONS SETTINGS
		#######################################################################################
						
		$themezee_settings[] = array("name" => "Twitter",
						"desc" => __('Enter the URL to your Twitter Profile here.', 'themezee_lang'),
						"id" => "themeZee_social_twitter",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "Facebook",
						"desc" => __('Enter the URL to your Facebook Profile here.', 'themezee_lang'),
						"id" => "themeZee_social_facebook",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "Google+",
						"desc" => __('Enter the URL to your Google+ profile.', 'themezee_lang'),
						"id" => "themeZee_social_googleplus",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "Pinterest",
						"desc" => __('Enter the URL to your Pinterest profile.', 'themezee_lang'),
						"id" => "themeZee_social_pinterest",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "LinkedIn",
						"desc" => __('Enter the URL to your LinkedIn Profile here.', 'themezee_lang'),
						"id" => "themeZee_social_linkedin",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");	
						
		$themezee_settings[] = array("name" => "Xing",
						"desc" => __('Enter the URL to your Xing Profile here.', 'themezee_lang'),
						"id" => "themeZee_social_xing",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "MySpace",
						"desc" => __('Enter the URL to your MySpace Profile here.', 'themezee_lang'),
						"id" => "themeZee_social_myspace",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");	
						
		$themezee_settings[] = array("name" => "Blogger",
						"desc" => __('Enter the URL to your Blogger Profile here.', 'themezee_lang'),
						"id" => "themeZee_social_blogger",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");	
						
		$themezee_settings[] = array("name" => "Tumblr",
						"desc" => __('Enter the URL to your Tumblr Blog here.', 'themezee_lang'),
						"id" => "themeZee_social_tumblr",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "Typepad",
						"desc" => __('Enter the URL to your Typepad Blog here.', 'themezee_lang'),
						"id" => "themeZee_social_typepad",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "Wordpress",
						"desc" => __('Enter the URL to your Wordpress.com Blog here.', 'themezee_lang'),
						"id" => "themeZee_social_wordpress",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "Gowalla",
						"desc" => __('Enter the URL to your Gowalla Profile here.', 'themezee_lang'),
						"id" => "themeZee_social_gowalla",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "Flickr",
						"desc" => __('Enter the URL to your Flickr Profile here.', 'themezee_lang'),
						"id" => "themeZee_social_flickr",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
					
		$themezee_settings[] = array("name" => "Soundcloud",
						"desc" => __('Enter the URL to your Soundcloud Profile here.', 'themezee_lang'),
						"id" => "themeZee_social_soundcloud",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "Spotify",
						"desc" => __('Enter the URL to your Spotify Profile here.', 'themezee_lang'),
						"id" => "themeZee_social_spotify",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "Last.fm",
						"desc" => __('Enter the URL to your Last.fm Profile here.', 'themezee_lang'),
						"id" => "themeZee_social_lastfm",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "Youtube",
						"desc" => __('Enter the URL to your Youtube Profile here.', 'themezee_lang'),
						"id" => "themeZee_social_youtube",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "Vimeo",
						"desc" => __('Enter the URL to your Vimeo Profile here.', 'themezee_lang'),
						"id" => "themeZee_social_vimeo",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "DeviantART",
						"desc" => __('Enter the URL to your DeviantART Profile here.', 'themezee_lang'),
						"id" => "themeZee_social_deviantart",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "Dribbble",
						"desc" => __('Enter the URL to your Dribbble Profile here.', 'themezee_lang'),
						"id" => "themeZee_social_dribbble",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
		
		$themezee_settings[] = array("name" => "Delicious",
						"desc" => __('Enter the URL to your Delicious Profile here.', 'themezee_lang'),
						"id" => "themeZee_social_delicious",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "Digg",
						"desc" => __('Enter the URL to your Digg Profile here.', 'themezee_lang'),
						"id" => "themeZee_social_digg",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "Reddit",
						"desc" => __('Enter the URL to your Reddit Profile here.', 'themezee_lang'),
						"id" => "themeZee_social_reddit",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "StumpleUpon",
						"desc" => __('Enter the URL to your StumpleUpon Profile here.', 'themezee_lang'),
						"id" => "themeZee_social_stumbleupon",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "RSS URL",
						"desc" => __('Enter your RSS URL (e.g. Feedburner Feed) here.', 'themezee_lang'),
						"id" => "themeZee_social_rss",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "Email",
						"desc" => __('Enter your Email URL (e.g. Feedburner Email Subscription) here.', 'themezee_lang'),
						"id" => "themeZee_social_email",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "Friendfeed",
						"desc" => __('Enter the URL to your Friendfeed Profile here.', 'themezee_lang'),
						"id" => "themeZee_social_friendfeed",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		$themezee_settings[] = array("name" => "Skype",
						"desc" => __('Enter your Skype Contact here.', 'themezee_lang'),
						"id" => "themeZee_social_skype",
						"std" => "",
						"type" => "text",
						"section" => "themeZee_buttons");
						
		return $themezee_settings;
	}

?>