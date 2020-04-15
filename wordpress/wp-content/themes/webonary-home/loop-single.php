			
			<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			
				<h2 class="post-title"><?php the_title(); ?></h2>
					
				<div class="postmeta"><?php do_action('themezee_display_postmeta_single'); ?></div>
				
				<div class="entry">
					<?php the_post_thumbnail('medium', array('class' => 'alignleft')); ?>
					<?php the_content(); ?>
					<div class="clear"></div>
					<?php wp_link_pages(); ?>
					<!-- <?php trackback_rdf(); ?> -->			
				</div>
				
				<div class="postinfo"><?php do_action('themezee_display_postinfo_single'); ?></div>

			</div>