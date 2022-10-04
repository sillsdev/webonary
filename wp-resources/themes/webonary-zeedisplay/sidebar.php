<div <?php if(!isMobile()) { ?> id="sidebar" <?php } ?>>
	<ul>
		<?php
		if (function_exists('webonary_searchform'))
			webonary_searchform(true);

		if (is_active_sidebar('sidebar-pages'))
			dynamic_sidebar('sidebar-pages');
		?>
	</ul>
</div>