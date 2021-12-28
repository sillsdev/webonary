<?php
$is_front_page = is_front_page();
$content_class = $is_front_page ? 'homepage' : '';
?>
<div id="page" class="row">
	<?php //get_sidebar(); ?>
	<div id="content <?php echo $content_class ?>">

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

			<div id="page-<?php the_ID(); ?>" <?php post_class(); ?>>

				<?php if (!$is_front_page) { ?>
					<h2><?php the_title(); ?></h2>
				<?php } ?>
				<div class="pageentry">
					<?php the_post_thumbnail('medium', array('class' => 'alignleft')); ?>
					<?php the_content(); ?>
					<div class="clear"></div>
					<?php wp_link_pages(); ?>
				</div>

			</div>

		<?php endwhile; ?>

		<?php endif; ?>

		<?php comments_template(); ?>

	</div>
</div>
