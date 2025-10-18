<?php 
  // Get Query Arguments for Featured Posts Slider
  $options = get_option('themezee_options');
  $slider_limit = intval($options['themeZee_slider_limit']);
  $slider_content = ($options['themeZee_slider_content'] == 'recent') ? 'date' : 'comment_count';
  $slider_category = $options['themeZee_slider_category']; 

  $query_arguments = array(
    'post_status' => 'publish',
    'ignore_sticky_posts' => true,
    'posts_per_page' => $slider_limit,
    'orderby' => $slider_content,
    'order' => 'DESC',
    'category_name' => $slider_category
  );
  query_posts($query_arguments);
?>
<?php /* ?>
  <div id="slide_panel">
    <h2 id="slide_head"><?php echo esc_attr($options['themeZee_slider_title']); ?></h2>
    <div id="slide_keys">
      <a id="slide_prev" href="#prev"><<</a>
      <a id="slide_next" href="#next">&gt;&gt;</a>
    </div>
  </div>
  <div class="clear"></div>
  <? */ ?>
  <div>
    
    <div id="slideshow"  style="background:#0065A5; color:#FFFFFF; height: 250px;" class="fullwidth">
    
    <?php if (have_posts()) : while (have_posts()) : the_post();
    
      get_template_part( 'loop', 'slide' );
    
    endwhile; ?>
    
    <?php endif; ?>
    
    </div>
  
  </div>
  <div class="clear"></div>
  
<?php
  //Reset Query
  wp_reset_query();
?>