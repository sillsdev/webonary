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
	<?php
	/*
	?>
	<button id="responsive-menu-button" class="responsive-menu-button responsive-menu-boring
         responsive-menu-accessible" type="button" aria-label="Menu">


    <span class="responsive-menu-box">
        <span class="responsive-menu-inner"></span>
    </span>

    </button>
	<div id="responsive-menu-container" class="slide-left">
	    <div id="responsive-menu-wrapper">
<ul id="responsive-menu" class=""><li id="responsive-menu-item-634264" class=" menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children responsive-menu-item responsive-menu-item-has-children"><a href="http://webonary.localhost/lubwisi/overview/" class="responsive-menu-item-link">Overview<div class="responsive-menu-subarrow">▼</div></a><ul class="responsive-menu-submenu responsive-menu-submenu-depth-1"><li id="responsive-menu-item-634271" class=" menu-item menu-item-type-post_type menu-item-object-page responsive-menu-item"><a href="http://webonary.localhost/lubwisi/overview/introduction/" class="responsive-menu-item-link">Introduction</a></li><li id="responsive-menu-item-634265" class=" menu-item menu-item-type-post_type menu-item-object-page responsive-menu-item"><a href="http://webonary.localhost/lubwisi/overview/abbreviations/" class="responsive-menu-item-link">Abbreviations</a></li><li id="responsive-menu-item-634266" class=" menu-item menu-item-type-post_type menu-item-object-page responsive-menu-item"><a href="http://webonary.localhost/lubwisi/overview/alphabet/" class="responsive-menu-item-link">Alphabet</a></li><li id="responsive-menu-item-634267" class=" menu-item menu-item-type-post_type menu-item-object-page responsive-menu-item"><a href="http://webonary.localhost/lubwisi/overview/copyright/" class="responsive-menu-item-link">Copyright</a></li><li id="responsive-menu-item-634268" class=" menu-item menu-item-type-post_type menu-item-object-page responsive-menu-item"><a href="http://webonary.localhost/lubwisi/overview/credits-acknowledgements/" class="responsive-menu-item-link">Credits &amp; acknowledgements</a></li><li id="responsive-menu-item-634270" class=" menu-item menu-item-type-post_type menu-item-object-page responsive-menu-item"><a href="http://webonary.localhost/lubwisi/overview/forward/" class="responsive-menu-item-link">Foreword</a></li><li id="responsive-menu-item-634269" class=" menu-item menu-item-type-post_type menu-item-object-page responsive-menu-item"><a href="http://webonary.localhost/lubwisi/overview/entries-explained/" class="responsive-menu-item-link">Entries explained</a></li></ul></li><li id="responsive-menu-item-634272" class=" menu-item menu-item-type-post_type menu-item-object-page menu-item-home current-menu-item page_item page-item-9429 current_page_item responsive-menu-item responsive-menu-current-item"><a href="http://webonary.localhost/lubwisi/" class="responsive-menu-item-link">Search</a></li><li id="responsive-menu-item-634273" class=" menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children responsive-menu-item responsive-menu-item-has-children"><a href="http://webonary.localhost/lubwisi/browse/" class="responsive-menu-item-link">Browse<div class="responsive-menu-subarrow">▼</div></a><ul class="responsive-menu-submenu responsive-menu-submenu-depth-1"><li id="responsive-menu-item-634276" class=" menu-item menu-item-type-post_type menu-item-object-page responsive-menu-item"><a href="http://webonary.localhost/lubwisi/browse/browse-vernacular-english/" class="responsive-menu-item-link">Browse Vernacular – English</a></li><li id="responsive-menu-item-634274" class=" menu-item menu-item-type-post_type menu-item-object-page responsive-menu-item"><a href="http://webonary.localhost/lubwisi/browse/browse-english-vernacular/" class="responsive-menu-item-link">Browse English – Vernacular</a></li><li id="responsive-menu-item-634275" class=" menu-item menu-item-type-post_type menu-item-object-page responsive-menu-item"><a href="http://webonary.localhost/lubwisi/browse/browse-reversal-index-2/" class="responsive-menu-item-link">Browse Reversal Index 2</a></li></ul></li></ul>                                                <div id="responsive-menu-search-box">
	    <form action="http://webonary.localhost/lubwisi" class="responsive-menu-search-form" role="search">
	        <input name="s" placeholder="Search" class="responsive-menu-search-box" type="search">
	    </form>
	</div>                                                <div id="responsive-menu-additional-content"></div>                        </div>
	</div>
	*/
	?>
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
			<div style="float:left; width: 230px; text-align:left;"><img src="<?php echo  get_template_directory_uri(); ?>/images/sil-icon.gif" style="vertical-align:middle;"> <span style="width: 20%; margin-left:10px;">© <?php echo "2013 - " . date("Y"); ?> <a href="http://www.sil.org" target="_blank">SIL International</a><sup>®</sup></span></div>
			<img src="<?php echo  get_template_directory_uri(); ?>/images/webonary-icon.png" style="vertical-align:middle;"> <span style="margin-right:30px;"><a href="http://www.webonary.org" target="_blank">Webonary.org</a></span>
			<div style="float:right; width: 200px; text-align:right;"><a href="https://www.webonary.org/sil-international-terms-of-service-for-webonary-org/?lang=<?php if (function_exists('qtranxf_init_language')) { echo qtranxf_getLanguage(); } else { echo "en"; } ?>" style="width:20%;"><?php _e("Terms of Service", ZEE_LANG); ?></a></div>
		</div>
	</div>
</div>
</body>
</html>
<?php
		//echo var_dump($wpdb->queries); ?>