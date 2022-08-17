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
	function __construct() {
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
	 *
	 * @param $args
	 * @param $instance
	 */
	function widget( $args, $instance ) {

		$special_characters = get_option('special_characters');
		//if special characters were set inside the Webonary settings, use those
		//else if set in Widget (legacy code), use that.
		if((trim($special_characters)) == "")
		{
			$characters = apply_filters('widget_characters', $instance['characters']);
			$arrChar = explode(",", $characters);

			echo $args['before_widget'] ?? '';
			?>
<script type="text/javascript">
<!--
function addchar(button)
{
    let searchfield = document.getElementById('s');
    let currentPos = theCursorPosition(searchfield);
    let origValue = searchfield.value;

    searchfield.value = origValue.substr(0, currentPos) + button.value.trim() + origValue.substr(currentPos);

    searchfield.focus();

    return true;
}

function theCursorPosition(ofThisInput) {
    // set a fallback cursor location
    let theCursorLocation = 0;

    // find the cursor location via IE method...
    if (document.selection) {
        ofThisInput.focus();
        let theSelectionRange = document.selection.createRange();
        theSelectionRange.moveStart('character', -ofThisInput.value.length);
        theCursorLocation = theSelectionRange.text.length;
    } else if (ofThisInput.selectionStart || ofThisInput.selectionStart === 0) {
        // or the FF way
        theCursorLocation = ofThisInput.selectionStart;
    }
    return theCursorLocation;
}
-->
</script>

			<?php
			$html = '<input class="button spbutton" type="button" value="%s" onClick="addchar(this)">';

			foreach ( $arrChar as $char ) {
				printf( $html, $char );
			}

			echo $args['after_widget'] ?? '';
		}
	}

	/**
	 * Widget options to be saved
	 *
	 * @param $new_instance
	 * @param $old_instance
	 *
	 * @return array
	 */
	function update($new_instance, $old_instance) {
		/*parent::update($new_instance, $old_instance);*/
		$instance = $old_instance;
		$instance['characters'] = strip_tags($new_instance['characters']);
		return $instance;
	}

	/**
	 * Widget admin
	 * @param $instance
	 */
	function form($instance) {

	    $id = $this->get_field_id('characters');
	    $name = $this->get_field_name('characters');
	    $value = $instance['characters'];

	    $html = <<<HTML
<p>
  <label for="%1\$s">Characters (separated by comma):</label>
  <input class="widefat" id="%1\$s" name="%2\$s" type="text" value="%3\$s">
</p>
HTML;
	    printf( $html, $id, $name, $value );
	}
}
