<?php
if(isset($_GET['s']))
{
	try {
		load_template(locate_template( 'search.php' )  );
	} catch (Exception $e) {
	    echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
}
else
{
?>

<?php get_header(); ?>

	<div style="100%;" id="page">
	<?php get_sidebar(); ?>
	<div id="content">

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

			<div id="page-<?php the_ID(); ?>" <?php post_class(); ?>>

					<?php if (! is_front_page()) { ?>
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
<?php get_footer();
}
?>
