<?php get_header(); ?>

	<div id="content">

	<!--- Post Starts -->
		
		<div class="page">
			
			<h2><?php _e('404 Error: Not found', ZEE_LANG); ?></h2>
				
			<div class="entry">
					<p><?php _e('The page you trying to reach does not exist, or has been moved. Please use the menus or the search box to find what you are looking for', ZEE_LANG); ?></p>
					<?php get_search_form(); ?>
					
					<?php wp_reset_query(); ?> 
		
					<h2><?php _e('Latest Posts', ZEE_LANG); ?></h2><br/>
					<ul>
					
					<?php query_posts('post_type="post"&post_status="publish"&showposts=9'); ?>
					<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
							<li><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></li>
						<?php endwhile; ?>
						<?php endif; ?>
						<?php wp_reset_query(); ?> 
					</ul>
					
					<h2><?php _e('Pages', ZEE_LANG); ?></h2><br/>
					<ul>
						<?php wp_list_pages('title_li='); ?>
					</ul>
				</div>
				
		</div>
			
		<!--- Post Ends -->
			
	</div>

	<?php get_sidebar(); ?>
<?php get_footer(); ?>	