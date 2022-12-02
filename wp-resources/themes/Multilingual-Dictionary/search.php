<?php get_header();
require("highlight-code.php");
?>
  <div id="bd" class="yui-navset">

      <div class="yui-b" id="secondary">
 
		<?php get_sidebar(); ?>
 
       </div>
	 	              
    <div id="yui-main">        
        <div class="yui-b" >
        <div class="rightsidebar" id="third"><?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('rightsidebar') ) : ?><?php endif; ?></div>
        <?php //<div class="yui-ge">?>
			<div class=maincontent><div class="yui-u first">					                         
        <?php 
        require("searchform.php");
        
        $query = stripslashes($_GET['s']);
        if (have_posts()) :        	
        ?>
				<div class="yui-ge">
				<?php
				echo $wp_query->found_posts . " "; 
				if(strlen($query) > 0)
        		{        				
				  printf(__('search results for %s:','dictrans'), "'" . $query . "'");
        		}
        		else
        		{
				 echo "search results:"; //doesn't seem to be used anymore...
        		}
        ?>
        </div>				
				<div id="searchresults">												           
                <?php while (have_posts()) : the_post(); ?>                    
                <div class="item entry" id="post-<?php the_ID(); ?>">
<!-- item -->
								<?php /* ?>
                                <div class="item entry" id="post-<?php the_ID(); ?>">
                                          <div class="itemhead">
                                            <h3><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h3>
                                            <div class="chronodata"><?php the_time('F jS, Y') ?> <!-- by <?php the_author() ?> --></div>
                                          </div>
                                 <?php */ ?>
                                         <div class="storycontent">                                         	
                                            <?php 
                                            $content = get_the_content();                                            
                                            //$content = ereg_replace(trim($query), "<span class=hilite>" . trim($query) . "</span>", $content);                                            
                                            echo $content; ?>
                                         </div>
                                         
                                     <?php /* ?>
                                          <small class="metadata">
                                                         <span class="category">Filed under: <?php the_category(', ') ?> </span> | <?php edit_post_link('Edit', '', ' | '); ?> <?php comments_popup_link('Comment (0)', ' Comment (1)', 'Comments (%)'); ?>
                                                  <?php if ( function_exists('wp_tag_cloud') ) : ?>
							 <?php the_tags('<span class="tags">Article tags: ', ', ' , '</span>'); ?>
							 <?php endif; ?>
												  </small>
                                 </div>
                                 <? */ ?>                                 
<!-- end item -->
				</div>
 
<?php //comments_template(); // Get wp-comments.php template ?>
 	
                <?php endwhile; ?>
                </div>            
                
				<div class="navigation">
                        <!--
                       <div class="alignleft"><?php next_posts_link('&laquo; Previous Entries') ?></div>
                        <div class="alignright"><?php previous_posts_link('Next Entries &raquo;') ?></div>
                        -->
                        <?php if(function_exists('wp_page_numbers')) { wp_page_numbers(); } ?>
                        <p> </p>
                </div> 
        <?php else : ?>
                <div class="yui-ge"><?php _e('No search results', 'dictrans'); ?>
	                <p><?php printf(__('We did not find any posts containing the string %s','dictrans'), "'" . $query . "'") ?>.</p>
                </div>
        <?php endif; ?>
<!-- end content -->

<!-- 2nd sidebar -->
 </div><!-- end yiu-u --> 
		                
        </div>       
        
  </div>
 
<?php get_footer(); ?>
<script language=JavaScript>
<!--
 	highlightSearchTerms('<?php echo trim($query); ?>');
//-->
</script>
