
<div id="sidebar">
	<ul>

<?php
	if(is_page() && is_active_sidebar('sidebar-pages')) : dynamic_sidebar('sidebar-pages');
    elseif(is_active_sidebar('sidebar-blog')) : dynamic_sidebar('sidebar-blog');
else : ?>

	<?php wp_list_categories('title_li=<h2>Categories</h2>'); ?>
	
	<?php wp_list_pages('title_li=<h2>Pages</h2>'); ?>

	<li><h2>Archives</h2>
		<ul>
		<?php wp_get_archives(); ?>
		</ul>
	</li>
	
	<?php wp_list_bookmarks(); ?>
	
<?php endif; ?>
	
	</ul>
</div>