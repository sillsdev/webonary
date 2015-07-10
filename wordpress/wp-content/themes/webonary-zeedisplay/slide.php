<?php 
	// Select Slider Modus
	$options = get_option('themezee_options');
	$slider_limit = intval($options['themeZee_slider_limit'] - 1);
	
	switch($options['themeZee_slider_content']) {
		case 0: query_posts('offset=0&posts_per_page=' . $slider_limit); break;
		case 1: query_posts('category_name=' . __('featured', ZEE_LANG) . '&offset=0&posts_per_page=' . $slider_limit); break;
		case 2: query_posts('meta_key=' . __('featured', ZEE_LANG) . '&meta_value=yes&offset=0&posts_per_page=' . $slider_limit); break;
		case 3: query_posts('cat=' . esc_attr($options['themeZee_slider_cat']) . '&offset=0&posts_per_page=' . $slider_limit); break;
		default: query_posts('offset=0&posts_per_page=' . $slider_limit); break;
	}
?>	

	<div id="slide_panel">
		<h2 id="slide_head"><?php echo esc_attr($options['themeZee_slider_title']); ?></h2>
		<div id="slide_keys">
			<a id="slide_prev" href="#prev">&lt;&lt;</a>
			<a id="slide_next" href="#next">&gt;&gt;</a>
		</div>
	</div>
	<div class="clear"></div>
	
	<div id="content-slider">
		
		<div id="slideshow">
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>	
			
			<div class="post">
			
				<h2><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h2>
					
				<div class="entry">
					<?php webonary_zeedisplay_display_entry_header(); ?>
					<?php webonary_zeedisplay_display_entry(); ?>

					<?php
					/* the_excerpt() was originally displayed here. It was
					 * removed because it removes HTML tags if no excerpt is
					 * provided and the first 55 words of the post content. are
					 * shown.
					<?php the_post_thumbnail('thumbnail', array('class' => 'alignleft')); ?>
					<?php the_excerpt(); */?>
					<div class="clear"></div>
				</div>
				
				<?php webonary_zeedisplay_display_entry_footer(); ?>

			</div>
		
		<?php endwhile; ?>
		<?php endif; ?>
		
		</div>
	
	</div>
	<div class="clear"></div>
	
<?php
	//Reset Query
	wp_reset_query();
?>