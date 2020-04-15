<?php
/**
 * Plugin Name: Character Look up Charts
 *
 * Provides a popup chart for the special characters above the search box
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

class popup_charts extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function __construct() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'searchform', 'description' => __('Add character lookup chart.', 'searchform') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'chart-popups' );

		/* Create the widget. */
		//$this->WP_Widget( 'chart-popups', __('Popup Charts Widget', 'searchform'), $widget_ops, $control_ops );
		parent::__construct( 'chart-popups', __('Popup Charts Widget', 'searchform'), $widget_ops, $control_ops );
	}

	/**
	 * Widget display.
	 */
	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);
		$popupWidth = $instance['popupWidth'];
		$popupHeight = $instance['popupHeight'];
		echo $before_widget;
		?>
			&nbsp;<a href="" id=popuppage1 style="text-decoration: underline;"
			 onclick="window.open(
				'/wp-content/plugins/character-chart/include/chart.php','popuppage1','width=<?php echo $popupWidth; ?>,height=<?php echo $popupHeight; ?>,top=50,left=200,scrollbars=yes');
				return false;"><?php echo $title; ?></a>
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
		$instance['characters'] = strip_tags($new_instance['characters']);
		$instance['fontfamily'] = $new_instance['fontfamily'];
		$instance['fontsize'] = $new_instance['fontsize'];
		$instance['numberOfCols'] = $new_instance['numberOfCols'];
		$instance['popupWidth'] = $new_instance['popupWidth'];
		$instance['popupHeight'] = $new_instance['popupHeight'];
        return $instance;	}

	/**
	 * Widget admin
	 * @param <type> $instance
	 */
	function form($instance) {
		/* parent::form($instance); */
        $title = esc_attr($instance['title']);
        $numberOfCols = 7;
        if(isset($instance['numberOfCols']))
        {
        	$numberOfCols = $instance['numberOfCols'];
        }
	 	$fontsize = "14px";
        if(isset($instance['fontsize']))
        {
        	$fontsize = $instance['fontsize'];
        }
        $popupHeight = 500;
        if(isset($instance['popupHeight']))
        {
        	$popupHeight = $instance['popupHeight'];
        }
        $popupWidth = 400;
        if(isset($instance['popupWidth']))
        {
        	$popupWidth = $instance['popupWidth'];
        }

        ?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>
					   " name="<?php echo $this->get_field_name('title'); ?>
					   " type="text" value="<?php echo $title; ?>" />
        	</p>
			<p>
				<label for="<?php echo $this->get_field_id('characters'); ?>">Characters
				(separated by comma):</label> <input class="widefat"
				id="<?php echo $this->get_field_id('characters'); ?>"
				name="<?php echo $this->get_field_name('characters'); ?>'" type="text"
				value="<?php echo $instance['characters']; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('fontfamily'); ?>">Font family:</label>
				<input class="widefat" id="<?php echo $this->get_field_id('fontfamily'); ?>"
				name="<?php echo $this->get_field_name('fontfamily'); ?>'" type="text"
				value="<?php echo $instance['fontfamily']; ?>" style="width:120px;"/>
				&nbsp;&nbsp;
				<label for="<?php echo $this->get_field_id('fontsize'); ?>">Size:</label>
				<input class="widefat" id="<?php echo $this->get_field_id('fontsize'); ?>"
				name="<?php echo $this->get_field_name('fontsize'); ?>'" type="text"
				value="<?php echo $fontsize; ?>" style="width:50px;"/>

			</p>
			<p>
				<label for="<?php echo $this->get_field_id('numberOfCols'); ?>">Number of Columns:</label>
				<input class="widefat" id="<?php echo $this->get_field_id('numberOfCols'); ?>"
				name="<?php echo $this->get_field_name('numberOfCols'); ?>'" type="text"
				value="<?php echo $numberOfCols; ?>" style="width:30px;" />
			</p>
<p>
				<label for="<?php echo $this->get_field_id('popupHeight'); ?>">Popup Height:</label>
				<input class="widefat" id="<?php echo $this->get_field_id('popupHeight'); ?>"
				name="<?php echo $this->get_field_name('popupHeight'); ?>'" type="text"
				value="<?php echo $popupHeight; ?>" style="width:40px;" />
				&nbsp;&nbsp;
				<label for="<?php echo $this->get_field_id('popupWidth'); ?>">Width:</label>
				<input class="widefat" id="<?php echo $this->get_field_id('popupWidth'); ?>"
				name="<?php echo $this->get_field_name('popupWidth'); ?>'" type="text"
				value="<?php echo $popupWidth; ?>" style="width:40px;" />
			</p>
        <?php 	}
}

?>