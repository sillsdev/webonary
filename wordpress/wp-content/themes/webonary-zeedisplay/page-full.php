<?php
/*
Template Name: Page Fullwidth
*/
?>
<?php get_header(); ?>

	<div <?php if(!isMobile()) { ?>class="fullwidth"<?php } ?>>

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

			<div id="page-<?php the_ID(); ?>" <?php post_class(); ?>>

				<div class="pageentry">
					<?php
					if(strpos(get_the_content(), "<h1") === false)
					{
					?>
						<h1><?php the_title(); ?></h1>
					<?php
					}
					?>
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