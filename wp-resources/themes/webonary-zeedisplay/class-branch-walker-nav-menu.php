<?php
/** @noinspection PhpMissingParamTypeInspection */


/**
 * Class Branch_Walker_Nav_Menu
 *
 * Special walker_nav_menu class to display the current branch.
 *
 * Usage: `[menu show_branch=1]` triggers this class.
 */
class Branch_Walker_Nav_Menu extends Walker_Nav_Menu
{
	public $tree_type = ['post_type', 'page_type', 'taxonomy', 'custom'];

	/**
	 * Display array of elements hierarchically.
	 *
	 * Does not assume any existing order of elements.
	 *
	 * NB: We are overriding `walk` so we only go through the elements one time.
	 *
	 * @param array $elements  An array of elements.
	 * @param int   $max_depth The maximum hierarchical depth.
	 * @param mixed ...$args   Optional additional arguments.
	 * @return string The hierarchical item output.
	 */
	public function walk($elements, $max_depth, ...$args)
	{
		$output = '';

		if (empty($elements))
			return $output;

		$item_id = $this->get_menu_id_of_current_post($elements);
		if (empty($item_id))
			return $output;

		// get the pages that are children of the current page
		$children_elements = array_filter($elements, function($e) use($item_id) { return $e->menu_item_parent == $item_id; });

		// doing this because the second parameter for `display_element` is by-ref
		$empty_array = [];

		foreach ($children_elements as $e ) {
			$this->display_element($e, $empty_array, 0, 0, $args, $output);
		}

		return $output;
	}

	/**
	 * Gets the menu ID of the current page
	 *
	 * @param array $elements Menu item post elements
	 *
	 * @return int
	 */
	private function get_menu_id_of_current_post(array $elements)
	{
		// this will get the ID from the posts table
		$post_id = get_the_ID();

		// for menu item posts, the ID of the original post is in the `object_id` field
		$posts = array_filter($elements, function($e) use($post_id) { return $e->object_id == $post_id; });

		// return zero if no matching posts found
		if (empty($posts))
			return 0;

		// return the menu ID of the current page
		return array_values($posts)[0]->db_id;
	}
}
