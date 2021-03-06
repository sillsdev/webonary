<?php
/**
 * Plugin Name: Yi Look up Charts
 *
 * Provides popup charts for the Yi Index and the Yi Radical Chart Index
 *
 * PHP version 5.2
 *
 * LICENSE GPL v2
 *
 * @package WordPress
 * @since 3.1
 */

// The WP class handles everything that needs to be handled with the widget:
// the settings, form, display, and update.  Nice!

class yi_popup_charts extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function yi_popup_charts() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'searchform', 'description' => __('Add Yi lookup charts.', 'searchform') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'yi-popups' );

		/* Create the widget. */
		$this->WP_Widget( 'yi-popups', __('Yi Popup Charts Widget', 'searchform'), $widget_ops, $control_ops );
	}

	/**
	 * Widget display.
	 */
	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		?>
			&nbsp;<a href="" id=popuppage1  <?php if(qtrans_getLanguage() == "ii") {?>class="nuosu"<?php }?> onclick="window.open(
				'/wp-content/plugins/yi-charts/include/idx_chart.html','popuppage1','width=990,height=610,top=50,left=200,scrollbars=yes');
				return false;"><?php _e('Yi index Chart', 'yi-popup'); ?></a>
			&nbsp;&nbsp;
			<a href="" id=popuppage2  <?php if(qtrans_getLanguage() == "ii") {?>class="nuosu"<?php }?> onClick="window.open('\
				/wp-content/plugins/yi-charts/include/idx_radical.html','popuppage2','width=470,height=880,top=50,left=200,scrollbars=yes');
				return false;"><?php _e('Yi Radical Stroke Index', 'yi-popup'); ?></a>
				<br style="margin-bottom:5px">
		<?php
		//<div style="height:5px"></div>
		echo $after_widget;
	}

	/**
	 * Widget options to be saved
	 * @param <type> $new_instance
	 * @param <type> $old_instance
	 */
	function update($new_instance, $old_instance) {
		/*parent::update($new_instance, $old_instance);*/
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
        return $instance;	}

	/**
	 * Widget admin
	 * @param <type> $instance
	 */
	function form($instance) {
		/* parent::form($instance); */
        $title = esc_attr($instance['title']);
        ?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>
					   " name="<?php echo $this->get_field_name('title'); ?>
					   " type="text" value="<?php echo $title; ?>" />
        </p>
        <?php 	}
}

?>