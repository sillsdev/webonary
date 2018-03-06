<?php
global $blog_id;
?>
<script language="javascript" type="text/javascript">
<!--
function popitup(url) {
	newwindow=window.open(url,'name','height=600,width=500,scrollbars=yes');
	if (window.focus) {newwindow.focus()}
	return false;
}
// -->
</script>
	<div style="clear: both;"></div>
<button id="responsive-menu-button"
	class="responsive-menu-button responsive-menu-boring
         responsive-menu-accessible"
	type="button" aria-label="Menu">

	<span class="responsive-menu-box"> <span class="responsive-menu-inner"></span>
	</span>

</button>
<?php
$menu_name = 'main';
$menu = wp_get_nav_menu_object($menu_name);
$menuitems = wp_get_nav_menu_items( $menu->term_id, array( 'order' => 'DESC' ) );
?>
<div id="responsive-menu-container" class="slide-left">
	<div id="responsive-menu-wrapper">
		<ul id="responsive-menu" class="">
	    <?php
		$count = 0;
		$submenu = false;
		foreach ( $menuitems as $index => $value)
		{
			$item = $menuitems[$index];
			$link = $item->url;
			$title = $item->title;
			// item does not have a parent so menu_item_parent equals 0 (false)
			if (!$item->menu_item_parent)
			{
				// save this id for later comparison with sub-menu items
				$parent_id = $item->ID;
				?>
			    <li id="responsive-menu-item-<?php echo $parent_id; ?>" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children responsive-menu-item <?php if($menuitems[$index+1]->menu_item_parent != 0) { echo "responsive-menu-item-has-children"; } ?>">
			    	<a href="<?php echo $link; ?>" class="responsive-menu-item-link">
			            <?php echo $title; ?>
			            <?php
			            if($menuitems[$index+1]->menu_item_parent != 0)
			            {
			            ?>
			            <div class="responsive-menu-subarrow">▼</div>
			            <?php
			            }
			            ?>
			         </a>
		        <?php
			}
		        if ( $parent_id == $item->menu_item_parent )
		        {
		        ?>
		            <?php
		            if ( !$submenu )
		            {
		            	$submenu = true; ?>
		            	<ul class="responsive-menu-submenu responsive-menu-submenu-depth-1">
		            <?php
		            } ?>

		                <li id="responsive-menu-item-<?php echo $item->ID; ?> class="menu-item menu-item-type-post_type menu-item-object-page responsive-menu-item">
		                <a href="<?php echo $link; ?>"
								class="responsive-menu-item-link"><?php echo $title; ?></a></li>

		            <?php if ( $menuitems[ $count + 1 ]->menu_item_parent != $parent_id && $submenu )
		            {
		            ?>
		            </ul>
		            <?php $submenu = false;
		            }
		            ?>

		        <?php
		        }

		    if ( $menuitems[ $count + 1 ]->menu_item_parent != $parent_id )
		    {
		    ?>
		    </li>
		    <?php $submenu = false;
		    }
		    ?>

		<?php $count++;
		}
		?>
		</ul>

		<div id="responsive-menu-search-box">
			<form action="/"
				class="responsive-menu-search-form" role="search">
				<input name="s" placeholder="Search"
					class="responsive-menu-search-box" type="search">
			</form>
		</div>
		<div id="responsive-menu-additional-content"></div>
	</div>
</div>
<?php wp_footer();?>
		<?php
		$options = get_option('themezee_options');
		if(is_active_sidebar('sidebar-footer')) : ?>
		<div id="bottombar">
			<ul>
				<?php dynamic_sidebar('sidebar-footer'); ?>
			</ul>
			<div style="clear: both;"></div>
		</div>
		<?php endif; ?>

		<?php

		if (has_nav_menu('top_navi'))
		{
			$color="pink";
		}
		else
		{
			$color="blue";
		}
		?>
		<div id="footer" class=<?php echo $color; ?> style="text-align:center; padding-bottom: 8px; font-size:12px;">
			<div style="text-align:center;"><?php echo  $options['themeZee_footer']; ?></div>
			<hr style="font-size:5px; margin-bottom: 4px; clear:both;">
			<div id="copyright">
				<div><img src="<?php echo  get_template_directory_uri(); ?>/images/sil-icon.gif" style="vertical-align:middle;"> <span style="width: 20%; margin-left:10px;">© <?php echo "2013 - " . date("Y"); ?> <a href="http://www.sil.org" target="_blank">SIL International</a><sup>®</sup></span></div>
				<div style="text-align:center;"><img src="<?php echo  get_template_directory_uri(); ?>/images/webonary-icon.png" style="vertical-align:middle;"> <span style="margin-right:30px;"><a href="http://www.webonary.org" target="_blank">Webonary.org</a></span></div>
				<div id=termsofservice><a href="https://www.webonary.org/sil-international-terms-of-service-for-webonary-org/?lang=<?php if (function_exists('qtranxf_init_language')) { echo qtranxf_getLanguage(); } else { echo "en"; } ?>" style="width:20%;"><?php _e("Terms of Service", ZEE_LANG); ?></a></div>
			</div>
		</div>
	</div>
</div>
</body>
</html>
<?php
		//echo var_dump($wpdb->queries); ?>