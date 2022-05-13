<?php

class Webonary2_Menu {

	public static function BootstrapMenu($location): string {

		$menu = null;
		$menu_items = null;

		// Get the nav menu based on the theme_location.
		$locations = get_nav_menu_locations();
		if ( $locations && isset( $locations[ $location ] ) ) {
			$menu = wp_get_nav_menu_object( $locations[ $location ] );
		}

		// Get the first menu that has items if we still can't find a menu.
		if (empty($menu)) {
			$menus = wp_get_nav_menus();
			foreach ( $menus as $menu_maybe ) {
				$menu_items = wp_get_nav_menu_items( $menu_maybe->term_id, array( 'update_post_term_cache' => false ) );
				if ( $menu_items ) {
					$menu = $menu_maybe;
					break;
				}
			}
		}

		if (empty($menu))
			return '';

		if (empty($menu_items))
			$menu_items = wp_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => false ) );

		// get a clean list of the menu items
		$items = [];
		$current_url = (isset( $_SERVER['HTTPS']) ? 'https' : 'http') . '://' .$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		foreach($menu_items as $menu_item) {

			$is_active = false;

			if ($menu_item->url && $menu_item->url == $current_url) {
				$is_active = true;
				$current_url = null;
			}

			$items[$menu_item->ID] = [
				'id' => $menu_item->ID,
				'title' => $menu_item->title,
				'href' => $menu_item->url,
				'active' => $is_active,
				'target' => $menu_item->target,
				'parent' => (int)$menu_item->menu_item_parent,
				'sub_items' => []
			];
		}

		unset($menu_item);
		unset($menu_items);
		unset($menu);

		// sort the list into sub-items
		foreach($items as $id => $item) {

			if ($item['parent'] === 0)
				continue;

			$items[$item['parent']]['sub_items'][] = $item;
			unset($items[$id]);
		}

		// fix drop-down items with href
		foreach($items as $id => &$item) {

			if (empty($item['sub_items']) || empty($item['href']))
				continue;

			array_unshift($item['sub_items'],
				[
					'id' => $item['id'],
					'title' => $item['title'],
					'href' => $item['href'],
					'active' => $item['active'],
					'target' => $item['target'],
					'parent' => $id,
					'sub_items' => []
				]
			);

			$item['href'] = '';
			$item['active'] = false;
		}

		unset($item);

		// build the HTML
		$item_str = '';
		foreach($items as $item) {
			$item_str .= self::ProcessMenuItem($item);
		}

		return $item_str;
	}

	/**
	 * @param array $menu_item
	 * @param bool $is_dropdown
	 *
	 * @return string
	 */
	private static function ProcessMenuItem(array $menu_item, bool $is_dropdown = false): string {

		$id = $menu_item['id'];
		$li_class = $is_dropdown ? '' : 'nav-item';

		if ($menu_item['active']) {
			$a_class = 'nav-link active';
			$active = 'aria-current="page"';
			$href = '#';
			$on_click = 'onclick="return false;"';
		}
		else {
			$a_class = $is_dropdown ? 'dropdown-item' : 'nav-link';
			$active = '';
			$href = $menu_item['href'];
			$on_click = '';
		}

		if (!empty($menu_item['sub_items'])) {

			$subitems = '';
			foreach($menu_item['sub_items'] as $item) {
				$subitems .= self::ProcessMenuItem($item, true);
			}

			return <<<HTML
<li class="$li_class dropdown" id="nav-item-$id">
  <a href="#" class="$a_class dropdown-toggle" target="{$menu_item['target']}" id="nav-link-$id" $active
     data-display="static" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{$menu_item['title']}</a>
  <ul class="dropdown-menu px-3 px-lg-0" aria-labelledby="nav-link-$id">   
    $subitems
  </ul>
</li>
HTML;
		}

		return <<<HTML
<li class="$li_class" id="nav-item-$id"><a href="$href" class="$a_class" target="{$menu_item['target']}" id="nav-link-$id" $on_click $active>{$menu_item['title']}</a></li>
HTML;
	}
}
