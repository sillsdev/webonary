<?php
/*
Template Name: Page Categories
*/
?>
<?php get_header(); ?>

	<div <?php if(!isMobile()) { ?>class="fullwidth"<?php } ?>>
	
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		
			<div id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
				
				<?php //echo "<h2>" . the_title() "</h2>"; ?>

				<div style="display:block; clear:both;">
					<?php the_post_thumbnail('medium', array('class' => 'alignleft')); ?>
					<?php the_content(); ?>
					<div class="clear"></div>
					<?php wp_link_pages(); ?>
				</div>
				
				
			</div>

		<?php endwhile; ?>

		<?php endif; ?>
		
	</div>

<?php get_footer(); ?>	