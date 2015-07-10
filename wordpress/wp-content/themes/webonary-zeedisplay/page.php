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

	<div align=center><?php if(isMobile()) { echo "<br>"; get_sidebar(); } ?></div>

	<div id="content" <?php if(!isMobile()) { ?>style="min-width:530px; width:530px;"<?php } ?>>
		
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
 
	<?php if(!isMobile()) { get_sidebar(); } ?>
<?php get_footer();
}
?>
