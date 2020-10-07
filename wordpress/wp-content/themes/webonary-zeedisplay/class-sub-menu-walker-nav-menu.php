<?php
/** @noinspection PhpMissingParamTypeInspection */


/**
 * Class Sub_Menu_Walker_Nav_Menu
 *
 * Special walker_nav_menu class to only display submenu.
 * Depth must be greater than zero.
 * NB: `show_submenu` specifies submenu to display.
 */
class Sub_Menu_Walker_Nav_Menu extends Walker_Nav_Menu
{
	/**
	 * @param object $element
	 * @param array $children_elements
	 * @param int $max_depth
	 * @param int $depth
	 * @param array $args
	 * @param string $output
	 */
	function display_element($element, &$children_elements, $max_depth, $depth, $args, &$output)
	{
		if ( !$element )
			return;

		$id_field = $this->db_fields['id'];

		$display_this_element = $depth != 0;
		if ($display_this_element) {
			//display this element
			if ( is_array( $args[0] ) )
				$args[0]['has_children'] = ! empty( $children_elements[$element->$id_field] );
			$cb_args = array_merge( array(&$output, $element, $depth), $args);
			call_user_func_array(array(&$this, 'start_el'), $cb_args);
		}

		$id = $element->$id_field;
		if ( is_array( $args[0] ) )
			$show_submenu=$args[0]['show_submenu'];
		else
			$show_submenu=$args[0]->show_submenu;

		$url = explode("/", $element->url);
		if(end($url) == "" || substr(end($url), 0, 1) == "?")
		{
			array_pop($url);
		}
		// descend only when the depth is right and there are children for this element
		$link = explode("?", end($url));
		if ( ($max_depth == 0 || $max_depth >= $depth+1 ) && isset( $children_elements[$id]) && $link[0]==$show_submenu) {

			foreach( $children_elements[ $id ] as $child ){

				if ( !isset($new_level) ) {
					$new_level = true;
					//start the child delimiter
					$cb_args = array_merge( array(&$output, $depth), $args);
					call_user_func_array(array(&$this, 'start_lvl'), $cb_args);
				}
				$this->display_element( $child, $children_elements, $max_depth, $depth + 1, $args, $output );
			}
			unset( $children_elements[ $id ] );
		}

		if ( isset($new_level) && $new_level ){
			//end the child delimiter
			$cb_args = array_merge( array(&$output, $depth), $args);
			call_user_func_array(array(&$this, 'end_lvl'), $cb_args);
		}

		if ($display_this_element) {
			//end this element
			$cb_args = array_merge( array(&$output, $element, $depth), $args);
			call_user_func_array(array(&$this, 'end_el'), $cb_args);
		}
	}
}
