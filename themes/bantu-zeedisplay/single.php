<?php get_header(); ?>

	<div id="content">

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		
			<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			
				<h2><?php the_title(); ?></h2>
					
				<?php webonary_zeedisplay_display_entry_header(); ?>

				<div class="entry">
					<?php the_post_thumbnail('medium', array('class' => 'alignleft')); ?>
					<?php the_content(); ?>
					<div class="clear"></div>
					<?php wp_link_pages(); ?>
					<!-- <?php trackback_rdf(); ?> -->			
				</div>
				
				<?php webonary_zeedisplay_display_entry_footer(); ?>

			</div>

		<?php endwhile; ?>

		<?php endif; ?>
			
		<?php comments_template(); ?>
		
	</div>
		
		<?php get_sidebar(); ?>
<?php get_footer(); ?>	