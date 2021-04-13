<?php get_header(); ?>

	<div id="content">

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		
			<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			
				<h2 class="post-title"><?php the_title(); ?></h2>

				<div class="postmeta"><?php do_action('themezee_display_postmeta_single'); ?></div>
				
				<div class="entry">
					<div class="attachment-entry">
						<a href="<?php echo wp_get_attachment_url($post->ID); ?>"><?php echo wp_get_attachment_image( $post->ID, 'full' ); ?></a>
						<?php if ( !empty($post->post_excerpt) ) the_excerpt(); ?>
						<?php the_content(); ?>
						<div class="clear"></div>
						<?php wp_link_pages(); ?>
					</div>
				</div>
				<div class="clear"></div>
				
			</div>

		<?php endwhile; ?>

		<?php endif; ?>
		
		<div id="image-nav">
			<span class="nav-previous"><?php previous_image_link( false, __( 'Previous' , 'themezee_lang' ) ); ?></span>
			<span class="nav-next"><?php next_image_link( false, __( 'Next' , 'themezee_lang' ) ); ?></span><div class="clear"></div>
			<span class="nav-return"><a href="<?php echo esc_url( get_permalink( $post->post_parent )); ?>" title="<?php _e('Return to Gallery', 'themezee_lang'); ?>" rel="gallery"><?php _e('Return to', 'themezee_lang'); ?> <?php echo get_the_title( $post->post_parent ); ?></a>
		</div>
				
		<?php comments_template(); ?>
		
	</div>
	
	<?php get_sidebar(); ?>
<?php get_footer(); ?>	