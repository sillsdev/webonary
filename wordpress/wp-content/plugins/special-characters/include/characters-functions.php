<?php
/**
 * Plugin Name: Special Character Buttons
 *
 * shows the buttons and handles the click events
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

class special_characters extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function special_characters() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'special_characters', 'description' => __('Add Special Characters.', 'special_characters') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'special_characters' );

		/* Create the widget. */
		//$this->WP_Widget( 'special_characters', __('Special Characters Widget', 'searchform'), $widget_ops, $control_ops );
		parent::__construct( 'special_characters', __('Special Characters Widget', 'searchform'), $widget_ops, $control_ops );
	}


	/**
	 * Widget display.
	 */
	function widget( $args, $instance ) {
		
		$special_characters = get_option('special_characters');
		//if special characters were set inside the Webonary settings, use those
		//else if set in Widget (legacy code), use that.
		if((trim($special_characters)) == "")
		{
			extract($args);
			$characters 		= apply_filters('widget_characters', $instance['characters']);
			$arrChar = explode(",", $characters);
	
			echo $before_widget;
			?>
			<script LANGUAGE="JavaScript">
			<!--
			function addchar(button)
			{
				var searchfield = document.getElementById('s');
				var currentPos = theCursorPosition(searchfield);
				var origValue = searchfield.value;
				var newValue = origValue.substr(0, currentPos) + button.value.trim() + origValue.substr(currentPos);
				 
				searchfield.value = newValue;
	
				searchfield.focus();
							
			    return true;
			}
	
			function theCursorPosition(ofThisInput) {
				// set a fallback cursor location
				var theCursorLocation = 0;
	
				// find the cursor location via IE method...
				if (document.selection) {
					ofThisInput.focus();
					var theSelectionRange = document.selection.createRange();
					theSelectionRange.moveStart('character', -ofThisInput.value.length);
					theCursorLocation = theSelectionRange.text.length;
				} else if (ofThisInput.selectionStart || ofThisInput.selectionStart == '0') {
					// or the FF way
					theCursorLocation = ofThisInput.selectionStart;
				}
				return theCursorLocation;
			}
			
			-->
			</script>
	
			<?php
				foreach($arrChar as $char)
				{
					?>
		<input
			id="spbutton" type="button" width="20" class="button"
			value="<?php echo $char; ?>" onClick="addchar(this)"
			style="padding: 5px">
					<?php
				}
			?>
			<?php
				
			echo $after_widget;
		}
	}

	/**
	 * Widget options to be saved
	 * @param <type> $new_instance
	 * @param <type> $old_instance
	 */
	function update($new_instance, $old_instance) {
		/*parent::update($new_instance, $old_instance);*/
		$instance = $old_instance;
		$instance['characters'] = strip_tags($new_instance['characters']);
		return $instance;
	}

	/**
	 * Widget admin
	 * @param <type> $instance
	 */
	function form($instance) {
		?>
<p><label for="<?php echo $this->get_field_id('characters'); ?>">Characters
(separated by comma):</label> <input class="widefat"
	id="<?php echo $this->get_field_id('characters'); ?>"
	name="<?php echo $this->get_field_name('characters'); ?>'" type="text"
	value="<?php echo $instance['characters']; ?>" /></p>
		<?php
	}
}
?>