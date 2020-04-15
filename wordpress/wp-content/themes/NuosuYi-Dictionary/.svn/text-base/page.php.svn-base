<?php get_header(); ?>
  <div id="bd" class="yui-navset">
	<div class="yui-b" id="secondary">
	<?php get_sidebar(); ?>
	</div>
	    
    <div id="yui-main">
		<div class="yui-b" >
			<?php //<div class="yui-ge">?>
			<div class=maincontent><div class="yui-u first">


<!-- item -->
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<div  id="post-<?php the_ID(); ?>">
				 <div class="itemhead">
			   	<table width="100%">
			   	<tr valign=top>
			   		<td>
			   			<h1 <?php if($qtransLanguage == "ii") {?>class="nuosu"<?php }?>><?php the_title(); ?></h1>
			   		</td>
			   		<td align=right width=200px>
			   			<nobr><?php echo qtrans_getLanguageLinks('text'); ?></nobr>
			   		</td>
			   	</tr>
			   	</table>  				 
				          
				 </div>
				 <div class="storycontent">

				<?php the_content(); ?>

				<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>

			</div>
		</div>
	<?php edit_post_link('Edit this entry.', '<p>', '</p>'); ?>
<!-- end item -->

		<?php endwhile; ?>
	<?php endif; ?>
<!-- end content -->
<!-- 2nd sidebar -->
</div>
<?php /* 
<!-- end yiu-u --><div class="yui-u" id="third"><?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('rightsidebar') ) : ?><h4>Extra Column</h4><p>You can fill this column by editing the index.php theme file. Or by Widget support.</p><?php endif; ?></div>
<!-- end 2nd sidebar -->
*/ ?>
			</div>
			
		</div>
	</div>
  </div>
<?php get_footer(); ?>