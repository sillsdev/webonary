<?php get_header(); ?>

	<div id="content">

		<?php if (is_category()) { ?><h2 class="arh"><?php _e('Archive for', ZEE_LANG); ?> <?php echo single_cat_title(); ?></h2>
		<?php } elseif (is_date()) { ?><h2 class="arh"><?php _e('Archive for', ZEE_LANG); ?> <?php the_time(get_option('date_format')); ?></h2>
		<?php } elseif (is_author()) { ?><h2 class="arh"><?php _e('Archive for', ZEE_LANG); ?> <?php the_author(); ?></h2>
		<?php } elseif (is_tag()) { ?><h2 class="arh"><?php _e('Tag Archive for', ZEE_LANG); ?> <?php echo single_tag_title('', true); ?></h2>
		<?php } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?><h2 class="arh"><?php _e('Archives', ZEE_LANG); ?></h2><?php } ?>

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<h2><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h2>

					<?php webonary_zeedisplay_display_entry_header(); ?>
					<?php webonary_zeedisplay_display_entry(); ?>
					<?php webonary_zeedisplay_display_entry_footer(); ?>
				</div>
			<?php endwhile; ?>

			<?php webonary_zeedisplay_page_navigation();	?>

		<?php endif; ?>
	</div>

	<?php get_sidebar(); ?>
<?php get_footer(); ?>