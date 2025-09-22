<?php
get_header();
include 'highlight-code.php';
?>
	<div style="padding: 10px 25px;">
	<div id="content">

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

			<div id="searchresults" <?php post_class(); ?>>

				<h2><?php //the_title(); ?></h2>

				<?php webonary_zeedisplay_display_entry_header(); ?>
				<div class="postentry">
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
	</div>
		<?php get_sidebar(); ?>
<?php get_footer(); ?>
<?php
if (isset($query) && strlen(trim($query)) > 0) {
    $trimmed = trim(str_replace('\'', '#', $query));
?>
<script type="text/javascript">
	jQuery("#searchresults")
		.highlight('<?php echo $trimmed; ?>', true)
		.highlight('<?php echo $trimmed; ?>', true);
</script>
<?php
}
