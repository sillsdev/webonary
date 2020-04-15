      
      <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      
        <h2 class="post-title"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h2>
      
        <div class="postmeta"><?php do_action('themezee_display_postmeta_index'); ?></div>
        
        <div class="entry">
          <?php the_post_thumbnail('thumbnail', array('class' => 'alignleft')); ?>
          <?php the_content('<span class="moretext">' . __('Read more', 'themezee_lang') . '</span>'); ?>
          <div class="clear"></div>
          <?php wp_link_pages(); ?>
        </div>

        <div class="postinfo"><?php do_action('themezee_display_postinfo_index'); ?></div>

      </div>