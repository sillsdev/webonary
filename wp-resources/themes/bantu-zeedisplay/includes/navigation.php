<?php
/*----------------------------------------------------------------------------*/

/**
 * Common page navigation
 */
function webonary_zeedisplay_page_navigation() {
	if(function_exists('wp_pagenavi')) { // if PageNavi is activated
		?>
		<div class="more_posts">
			<?php wp_pagenavi(); // Use PageNavi ?>
		</div>
	<?php
	} else { // Otherwise, use traditional Navigation ?>
		<div class="more_posts">
			<span class="post_links">
				<?php previous_posts_link (__('&laquo; Previous Entries', ZEE_LANG)) ?>
				<?php next_posts_link(__('Next Entries &raquo;', ZEE_LANG))&nbsp;  ?>
			</span>
		</div>
	<?php
	}
}
?>
