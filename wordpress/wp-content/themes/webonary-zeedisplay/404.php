<?php get_header(); ?>

	<div align=center><?php if(isMobile()) { echo "<br>"; get_sidebar(); } ?></div>

	<div id="content" <?php if(!isMobile()) { ?>style="min-width:530px; width:530px;"<?php } ?>>
		
			
			<div id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
				
				<div class="pageentry">
					<?php if (! is_front_page()) { ?>
					<h3><?php echo gettext("Entry not found."); ?></h3>
					<?php } ?>
				
					<?php the_post_thumbnail('medium', array('class' => 'alignleft')); ?>
					<?php the_content(); ?>
					<div class="clear"></div>
					<?php wp_link_pages(); ?>
				</div>

			</div>
		
	</div>
 
	<?php if(!isMobile()) { get_sidebar(); } ?>
<?php get_footer();
?>
