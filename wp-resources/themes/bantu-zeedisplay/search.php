<?php get_header(); 
require("highlight-code.php");
?>
<script type="text/javascript">
function openImage(image)
{
	window.open('<?php echo get_bloginfo("template_directory"); ?>/image.php?img=' + image,'popuppage1','width=500,height=400,top=50,left=200,scrollbars=yes');	
}
</script>

	<div id="content">
		<h2 class="arh"><?php _e('Search results', ZEE_LANG); ?></h2>
		<?php if (have_posts()) : 
		$query = stripslashes($_GET['s']);
		//echo $wp_query->found_posts . " "; 
	  	//echo getstring("search-results-for-s", "'" . $query . "'"); 
		?>			
		<div id="searchresults">		
			<?php while (have_posts()) : the_post(); ?>
				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>			
					<?php webonary_zeedisplay_display_entry_header(); ?>
					<?php webonary_zeedisplay_display_entry(); ?>
					<?php webonary_zeedisplay_display_entry_footer(); ?>
				</div>				
			<?php endwhile; ?>			
		</div>
			<?php if(function_exists('wp_page_numbers')) { wp_page_numbers(); } ?>
			<?php //webonary_zeedisplay_page_navigation();	?>
			
		<?php else : ?>
			<div class="post">
				<div class="entry">
					<p><?php _e('No matches. Please try again, or use the navigation menus to find what you search for.', ZEE_LANG); ?></p>
				</div>
			</div>

		<?php endif; ?>	
	</div>
		
<?php get_sidebar(); ?>
<?php get_footer(); ?>
<script language=JavaScript>
<!--
 	highlightSearchTerms('<?php echo trim($query); ?>');
//-->
</script>
