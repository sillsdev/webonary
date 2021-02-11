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

		<?php if (have_posts()) : while (have_posts()) : the_post();
		
			get_template_part( 'loop', 'single' );
		
		endwhile; ?>

		<?php endif; ?>
		
		<?php comments_template(); ?>
		
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