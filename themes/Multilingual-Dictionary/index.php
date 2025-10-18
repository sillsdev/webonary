<?php get_header(); ?>
  <div id="bd" class="yui-navset">

	<div class="yui-b" id="secondary">
	<?php get_sidebar(); ?>
	</div>
		    
    <div id="yui-main">
    
		<div class="yui-b">
		<div class="rightsidebar" id="third"><?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('rightsidebar') ) : ?><?php endif; ?></div>
			<?php //<div class="yui-ge">?>
			<div class="maincontent">
			<div class="yui-u first">

	<?php 
	require("searchform.php");
	
	if (function_exists('get_a_post')) { 
		get_a_post('home'); 
	}	
	
	the_content(); 
	?>
<!-- 2nd sidebar -->

</div><!-- end yiu-u -->
 
<!-- end 2nd sidebar -->
			</div>			
			
		</div>
		
	</div>
		
  </div>
    
<?php get_footer(); ?>