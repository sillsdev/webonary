    
      <div>
        <?php /* ?><h2 class="page-title"><?php the_title(); ?></h2><? */ ?>
        <?php /* ?><div class="postmeta"><?php do_action('themezee_display_postmeta_index'); ?></div> */ ?>
        
        <div class="entry">
          <?php the_post_thumbnail('thumbnail', array('class' => 'alignleft')); ?>
          <?php //the_excerpt(); 
          the_content();
          ?>
          <div class="clear"></div>
        </div>

        <?php /* ?><div class="postinfo"><?php do_action('themezee_display_postinfo_index'); ?></div> <? */ ?>

      </div>