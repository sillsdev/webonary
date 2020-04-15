<div id="sidebar" <?php if(is_front_page()) { echo "class=homepage"; }?>>
	<ul>

<?php
	//if(is_page() && is_active_sidebar('sidebar-pages')) :
	dynamic_sidebar('sidebar-pages');
    //elseif(is_active_sidebar('sidebar-blog')) : dynamic_sidebar('sidebar-blog');
	?>
	</ul>
</div>