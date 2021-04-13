<?php
/*
 * searchform supplied by plugin sil-dictionary
 */
if (function_exists('webonary_searchform'))
	webonary_searchform();

/*
 * Default searchform. Code from get_search_form() in WordPress's general-template.php.
 */
else {
	$form = '<form role="search" method="get" id="searchform" action="' . home_url( '/' ) . '" >
		<div><label class="screen-reader-text" for="s">' . __('Search for:') . '</label>
		<input type="text" value="' . get_search_query() . '" name="s" id="s" />
		<input type="submit" id="searchsubmit" value="'. esc_attr__('Search') .'" />
		</div>
		</form>';
	echo apply_filters('get_search_form', $form);
}
?>