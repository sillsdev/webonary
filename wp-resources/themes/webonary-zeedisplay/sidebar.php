<div <?php if(!isMobile()) { ?> id="sidebar" <?php } ?>>
	<ul>

<?php

	echo "<li id=search-2 class=widget widget_search>";
	if(function_exists('webonary_searchform')) { webonary_searchform(); }
	echo "</li>";
	
	//if(is_page() && is_active_sidebar('sidebar-pages')) : dynamic_sidebar('sidebar-pages');
	if(is_active_sidebar('sidebar-pages')) : dynamic_sidebar('sidebar-pages');
    //elseif(is_active_sidebar('sidebar-blog')) : dynamic_sidebar('sidebar-blog');
	endif; ?>
	
	</ul>
</div>