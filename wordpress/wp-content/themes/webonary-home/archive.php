<?php get_header(); ?>
<?php
if(!isMobile() || (isMobile() && !is_front_page()))
{
?>
	<style>
		#sidebar
		{
		   margin-left: 585px;
		}
	</style>

	<div id="content">
		
		<?php if (is_category()) { ?><h2 class="arh"><?php _e('Archive for', 'themezee_lang'); ?> <?php echo single_cat_title(); ?></h2>
		<?php } elseif (is_date()) { ?><h2 class="arh"><?php _e('Archive for', 'themezee_lang'); ?> <?php the_time(get_option('date_format')); ?></h2>
		<?php } elseif (is_author()) { ?><h2 class="arh"><?php _e('Author Archive', 'themezee_lang'); ?></h2>
		<?php } elseif (is_tag()) { ?><h2 class="arh"><?php _e('Tag Archive for', 'themezee_lang'); ?> <?php echo single_tag_title('', true); ?></h2>
		<?php } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?><h2 class="arh"><?php _e('Archives', 'themezee_lang'); ?></h2><?php } ?>
		
		<?php if (have_posts()) : while (have_posts()) : the_post();
		
			get_template_part( 'loop', 'index' );
		
		endwhile; ?>

			<?php if(function_exists('wp_pagenavi')) { // if PageNavi is activated ?>
				<div class="more_posts">
					<?php wp_pagenavi(); ?>
				</div>
			<?php } else { // Otherwise, use traditional Navigation ?>
				<div class="more_posts">
					<span class="post_links"><?php next_posts_link(__('&laquo; Older Entries', 'themezee_lang')) ?> &nbsp; <?php previous_posts_link (__('Recent Entries &raquo;', 'themezee_lang')) ?></span>
				</div>
			<?php }?>
			

		<?php endif; ?>
			
	</div>
		
	<?php
}
else
{
?>
<div align=center style="width: 100%;">
<?php
}
?>
	<?php
	if((isMobile() && is_front_page()) || !isMobile())
	{
		get_sidebar();
	}
	
if(isMobile())
{
	echo "</div>";
}
?>
	
<?php get_footer(); ?>