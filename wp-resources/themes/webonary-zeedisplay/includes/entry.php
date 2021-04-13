<?php
/*----------------------------------------------------------------------------*/

/**
 * Display common postmeta information. The parent theme originally showed
 * date, author, and a comment link.
 */
function webonary_zeedisplay_display_entry_header() {
	$options = get_option('themezee_options');
	if(isset($options['themeZee_blog_mode']) and $options['themeZee_blog_mode'] == BLOG_MODE) {
		?>
		<div class="postmeta">
			<span class="date"><a href="<?php the_permalink() ?>"><?php the_time(get_option('date_format')); ?></a></span>
			<span class="author"><?php the_author(); ?> </span>
			<span class="comment"><a href="<?php the_permalink() ?>#comments"><?php comments_number(__('No comments', ZEE_LANG),__('One comment',ZEE_LANG),__('% comments',ZEE_LANG)); ?></a></span>
		</div>
		<?php
	}
}
/*----------------------------------------------------------------------------*/

/**
 * Display common post entry. Note that single.php and slide.php has a slightly
 * different layout. Changes here won't be seen in those places, and perhaps
 * not elsewhere, either.
 */
function webonary_zeedisplay_display_entry() {
	?>
	<div class="postentry">
		<?php the_post_thumbnail('thumbnail', array('class' => 'alignleft')); ?>
		<?php the_content('<span class="moretext">' . __('Read more', ZEE_LANG) . '</span>'); ?>
		<div class="clear"></div>
	</div>
	<?php
}
/*----------------------------------------------------------------------------*/

/**
 * Display common post footer information. The parent theme originally showed
 * category and tags. Keeping <div class="postinfo"> without anything else keeps
 * a small bubble divider.
 */
function webonary_zeedisplay_display_entry_footer() {
	$options = get_option('themezee_options');
	if(isset($options['themeZee_blog_mode']) and $options['themeZee_blog_mode'] == BLOG_MODE) {
		?>
		<div class="postinfo">
			<!-- Category display -->
			<span class="folder"><?php the_category(', ') ?> </span>

			<!-- Tag display -->
			<span class="tag"><?php if (get_the_tags()) the_tags('', ', '); ?></span>
		</div>
		<?php
	}
}
?>