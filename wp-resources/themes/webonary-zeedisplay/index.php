<?php get_header(); ?>

		<div id="content">
		<?php
		if(isMobile()) {
			get_sidebar();
		}
		?>
		<?php
		/*
		 * If the slider is turned on, we'll show posts there, and not get into
		 * The Loop.
		 */
		$options = get_option('themezee_options');
		if(is_home() and isset($options['themeZee_show_slider']) and $options['themeZee_show_slider'] == 'true') {
				locate_template('/slide.php', true);
			}

		/*
		 * If the slider is turned off, we'll display entries as normal.
		 */
		else {
			?>
			<?php
			if (have_posts()) : while (have_posts()) : the_post(); ?>
					<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<h2><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h2>

						<?php
						webonary_zeedisplay_display_entry_header();
						webonary_zeedisplay_display_entry();
						webonary_zeedisplay_display_entry_footer();
						?>

					</div>
				<?php endwhile;

				webonary_zeedisplay_page_navigation();
			endif;
			}
			?>
		</div>

	<?php
	if(!isMobile()) {
		get_sidebar();
	}
	?>
<?php get_footer(); ?>