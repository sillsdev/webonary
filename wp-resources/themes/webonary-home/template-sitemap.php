<?php
/*
Template Name: Page Sitemap
*/
?>
<?php get_header(); ?>

	<div id="content">
		
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		
			<div id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
				
				<h2 class="page-title"><?php the_title(); ?></h2>
				<?php edit_post_link(__( 'Edit', 'themezee_lang' )); ?>

				<div class="entry">
					<?php the_post_thumbnail('medium', array('class' => 'alignleft')); ?>
					<?php the_content(); ?>
					<div class="clear"></div>

		<?php endwhile; ?>

		<?php endif; ?>
		
		<?php wp_reset_query(); ?> 
		
				<h2><?php _e('Latest Posts', 'themezee_lang'); ?></h2><br/>
				<ul>
					
				<?php query_posts('post_type="post"&post_status="publish"&showposts=9'); ?>
				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
						<li><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></li>
					<?php endwhile; ?>
					<?php endif; ?>
					<?php wp_reset_query(); ?> 
				</ul>
					
				<h2><?php _e('Pages', 'themezee_lang'); ?></h2><br/>
				<ul>
					<?php wp_list_pages('title_li='); ?>
				</ul>
					
				<h2><?php _e('Categories', 'themezee_lang'); ?></h2><br/>
				<ul>
					<?php wp_list_categories('title_li=&show_count=1'); ?>
				</ul>
					
				<h2><?php _e('Archives', 'themezee_lang'); ?></h2><br/>
				<ul>
					<?php wp_get_archives('show_post_count=true'); ?>
				</ul>
					
				<h2><?php _e('Posts by Category', 'themezee_lang'); ?></h2><br/>
					<?php $categories = get_categories( $args ); ?>
					<?php foreach($categories as $cat) : ?>
						<strong><?php _e('Category', 'themezee_lang'); ?>: <a href="<?php echo get_category_link( $cat->term_id ); ?>"><?php echo $cat->name; ?></a></strong>
							<ul>
							<?php query_posts('post_type="post"&post_status="publish"&cat='. $cat->term_id); ?>
							<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
									<li><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></li>
								<?php endwhile; ?>
								<?php endif; ?>
								<?php wp_reset_query(); ?> 
							</ul>
					
					<?php endforeach; ?>	
			</div>
		</div>					
	</div>

	<?php get_sidebar(); ?>
<?php get_footer(); ?>	