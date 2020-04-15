<?php
	// Add Theme 125x125 Ads
	class Theme_SocialMedia_Widget extends WP_Widget {
		function Theme_SocialMedia_Widget() {
			$widget_ops = array('classname' => 'theme_socialmedia', 'description' => __('Show your Social Media Buttons', ZEE_LANG) );
			$this->WP_Widget('theme_socialmedia', 'zeeSocialMedia Buttons', $widget_ops);
		}
	 
		function widget($args, $instance) {
			extract($args, EXTR_SKIP);
			$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
			
			$url = get_template_directory_uri() . '/images/icons';
			$options = get_option('themezee_options');
			
			// Output
			echo $before_widget;
			if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
		?>
			<div id="socialmedia_buttons">
				
				<?php // RSS Button
				if ( isset($options['themeZee_rss']) and $options['themeZee_rss'] <> '' ) { ?>
					<a href="<?php echo esc_url($options['themeZee_rss']); ?>"><img src="<?php echo $url; ?>/rss.png" alt="rss" /></a>
				<?php } ?>
				
				<?php // Email Button
				if ( isset($options['themeZee_email']) and $options['themeZee_email'] <> '' ) { ?>
					<a href="<?php echo esc_url($options['themeZee_email']); ?>"><img src="<?php echo $url; ?>/email.png" alt="email" /></a>
				<?php } ?>
				
				<?php // Twitter Button
				if ( isset($options['themeZee_twitter']) and $options['themeZee_twitter'] <> '' ) { ?>
					<a href="<?php echo esc_url($options['themeZee_twitter']); ?>"><img src="<?php echo $url; ?>/twitter.png" alt="twitter" /></a>
				<?php } ?>
				
				<?php // Facebook Button
				if ( isset($options['themeZee_facebook']) and $options['themeZee_facebook'] <> '' ) { ?>
					<a href="<?php echo esc_url($options['themeZee_facebook']); ?>"><img src="<?php echo $url; ?>/facebook.png" alt="facebook" /></a>
				<?php } ?>
				
				<?php // Tumblr Button
				if ( isset($options['themeZee_tumblr']) and $options['themeZee_tumblr'] <> '' ) { ?>
					<a href="<?php echo esc_url($options['themeZee_tumblr']); ?>"><img src="<?php echo $url; ?>/tumblr.png" alt="tumblr" /></a>
				<?php } ?>
				
				<?php // LinkedIn Button
				if ( isset($options['themeZee_linkedin']) and $options['themeZee_linkedin'] <> '' ) { ?>
					<a href="<?php echo esc_url($options['themeZee_linkedin']); ?>"><img src="<?php echo $url; ?>/linkedin.png" alt="linkedin" /></a>
				<?php } ?>
				
				<?php // Xing Button
				if ( isset($options['themeZee_xing']) and $options['themeZee_xing'] <> '' ) { ?>
					<a href="<?php echo esc_url($options['themeZee_xing']); ?>"><img src="<?php echo $url; ?>/xing.png" alt="xing" /></a>
				<?php } ?>
				
				<?php // Delicious Button
				if ( isset($options['themeZee_delicious']) and $options['themeZee_delicious'] <> '' ) { ?>
					<a href="<?php echo esc_url($options['themeZee_delicious']); ?>"><img src="<?php echo $url; ?>/delicious.png" alt="delicious" /></a>
				<?php } ?>
				
				<?php // Digg Button
				if ( isset($options['themeZee_digg']) and $options['themeZee_digg'] <> '' ) { ?>
					<a href="<?php echo esc_url($options['themeZee_digg']); ?>"><img src="<?php echo $url; ?>/digg.png" alt="digg" /></a>
				<?php } ?>
				
				<?php // Flickr Button
				if ( isset($options['themeZee_flickr']) and $options['themeZee_flickr'] <> '' ) { ?>
					<a href="<?php echo esc_url($options['themeZee_flickr']); ?>"><img src="<?php echo $url; ?>/flickr.png" alt="flickr" /></a>
				<?php } ?>
				
				<?php // Youtube Button
				if ( isset($options['themeZee_youtube']) and $options['themeZee_youtube'] <> '' ) { ?>
					<a href="<?php echo esc_url($options['themeZee_youtube']); ?>"><img src="<?php echo $url; ?>/youtube.png" alt="youtube" /></a>
				<?php } ?>
				
				<?php // Vimeo Button
				if ( isset($options['themeZee_vimeo']) and $options['themeZee_vimeo'] <> '' ) { ?>
					<a href="<?php echo esc_url($options['themeZee_vimeo']); ?>"><img src="<?php echo $url; ?>/vimeo.png" alt="vimeo" /></a>
				<?php } ?>
			</div>
		<?php
			echo $after_widget;
		}
	 
		function update($new_instance, $old_instance) {
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			return $instance;
		}
	 
		function form($instance) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
			$title = strip_tags($instance['title']);
		?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', ZEE_LANG); ?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<?php
		}
	}
	register_widget('Theme_SocialMedia_Widget');
?>