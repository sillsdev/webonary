<?php
	// Add Theme Twitter Widget
	class themezee_Ads_Widget extends WP_Widget {

		function __construct() {
			$widget_ops = array('classname' => 'themezee_ads', 'description' => __('Show your 125x125 Banner Ads', 'themezee_lang') );
			//$this->WP_Widget('themezee_ads', 'ThemeZee Banner Ads Widget', $widget_ops);
			parent::__construct('themezee_ads', 'ThemeZee Banner Ads Widget', $widget_ops);
		}

		function widget($args, $instance) {
			extract( $args );
			$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);

			$pic = array();
			$url = array();
			$nr = range(1,8);
			$i = 0;
			$n = 0;

			if( isset($instance['target']) and $instance['target'] == 1)
				$target = 1;
			else
				$target = 0;

			$options = get_option('themezee_options');
			if ( isset($options['themeZee_ads_rotate']) and $options['themeZee_ads_rotate'] == 'true' ) {
				shuffle($nr);
			}
			foreach ($nr as $number) {
				if( isset($options['themeZee_ads_image_'.$number]) and $options['themeZee_ads_image_'.$number] != '' ) {
					$i++;
					$pic[$i] = esc_url($options['themeZee_ads_image_'.$number]);
					$url[$i] = esc_url( $options['themeZee_ads_url_'.$number]);
				}
			}

			// Output
			echo $before_widget;
			if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
		?>
			<div class="widget-ads">
				<?php
				if(count($pic) > 0) {
					foreach($pic as $key) {
						$n++;
				?>
					<a <?php if($target == 1) { echo 'target="_blank"'; } ?> href="<?php echo $url[$n]; ?>"><img src="<?php echo $pic[$n]; ?>" alt="banner" /></a>
			<?php
					}
				}
				else {
					_e('Go to WP-Admin-> Appearance-> Theme Options to configure this widget', 'themezee_lang');
				}
			?>
			</div>
		<?php
			echo $after_widget;
		}

		function update($new_instance, $old_instance) {
			$instance = $old_instance;
			$instance['title'] = isset($new_instance['title']) ? esc_attr($new_instance['title']) : '';
			$instance['target'] = isset($new_instance['target']) ? 1 : 0;
			return $instance;
		}

		function form($instance) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
			$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
			$target = (isset($instance['target']) and $instance['target'] == 1) ? 'checked="checked"' : '';
		?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'themezee_lang'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>

			<p><input class="checkbox" type="checkbox" <?php echo $target; ?> id="<?php echo $this->get_field_id('target'); ?>" name="<?php echo $this->get_field_name('target'); ?>" />
			<label for="<?php echo $this->get_field_id('target'); ?>"><?php _e('Open links in a new window', 'themezee_lang'); ?></label></p>
	<?php
		}
	}
	register_widget('themezee_Ads_Widget');
?>