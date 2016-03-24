<?php
class number_of_entries extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function number_of_entries() {
		/* Widget settings. */
		$widget_ops = array('classname' =>  'number_of_entries', 'description' => __( "Display Number of Dictionary Entries") );
		
		$this->WP_Widget('number_of_entries', __('Number of Dictionary Entries'), $widget_ops);
		
	}
	
	function form( $instance ) {

		$title = isset($instance['title']) ? esc_attr($instance['title']) : ''; ?>
		
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		
		<input id="<?php echo  $this->get_field_id('title'); ?>" name="<?php echo  $this->get_field_name('title'); ?>" type="text" value="<?php  echo $title; ?>" /></p>
	<?php
	}
		
	/**
	 * Widget display.
	 */
	function widget( $args, $instance ) {
		
		extract($args);
		
		
		
		$title = apply_filters('widget_title',
				empty($instance['title']) ? __('Number of Entries') : $instance['title'],  $instance, $this->id_base);
		
		
		
		echo $before_widget;
		
		
		
		if ( $title ) echo $before_title . $title . $after_title;
		
		$import = new sil_pathway_xhtml_Import();
		
		$arrIndexed = $import->get_number_of_entries();
		
		$text = "";
		foreach($arrIndexed as $indexed)
		{
			$text .= $indexed->language_name . ":&nbsp;". $indexed->totalIndexed;
			$text .= "<br>";
		}
		echo $text;
	}
}

add_action( 'widgets_init', 'register_number_of_entries_widget' );

// Register the class as a widget
function register_number_of_entries_widget() {
	register_widget( 'number_of_entries' );
}