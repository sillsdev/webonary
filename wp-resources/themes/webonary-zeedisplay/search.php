<?php
/** @noinspection PhpMultipleClassDeclarationsInspection */
get_header();

$rtl = get_option('vernacularRightToLeft') == '1';
$align_class = $rtl ? 'right' : 'left';
?>
<script type="text/javascript">
function openImage(image)
{
	window.open('<?php echo get_bloginfo('template_directory'); ?>/image.php?img=' + image,'popuppage1','width=500,height=400,top=50,left=200,scrollbars=yes');
}
</script>

 	<div style="padding: 10px 25px;">
	<div id="content">
		<?php
		$search_query = '';
		$search = filter_input(INPUT_GET, 's', FILTER_SANITIZE_STRING, array('options' => array('default' => '')));
		if($search !== '')
		{
			$search_query = get_search_query();
		}
		else
		{
			$sem_domain = filter_input(INPUT_GET, 'semdomain', FILTER_SANITIZE_STRING, array('options' => array('default' => '')));
			if($sem_domain !== '')
			{
				$search_query = $sem_domain;
			}
			else
			{
				$taxonomy = filter_input(INPUT_GET, 'tax', FILTER_SANITIZE_STRING, array('options' => array('default' => '')));
				if (get_option('useCloudBackend'))
				{
					$search_query = $taxonomy;
				}
				else
				{
					$term = get_term($taxonomy, 'sil_semantic_domains');
					$search_query = isset($term) ? $term->name : $taxonomy;
				}
			}
		}
		?>
		<h2 class="arh"><?php printf( __('Search results for "%s"', ZEE_LANG), $search_query);?></h2>
		<?php

		$query = filter_input(INPUT_GET, 's', FILTER_UNSAFE_RAW, ['options' => ['default' => '']]);

		//search string are normalized to NFC
		if (class_exists('Normalizer', false))
			$query = normalizer_normalize($query, Normalizer::FORM_C);

        if (have_posts()) : ?>
			<div id="searchresults" class="<?php echo $align_class; ?>">
				<?php while (have_posts()) : the_post(); ?>
					<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<?php webonary_zeedisplay_display_entry_header(); ?>
						<?php webonary_zeedisplay_display_entry(); ?>
						<?php webonary_zeedisplay_display_entry_footer(); ?>
					</div>
				<?php
				if( comments_open() ) {
				?>
					<a href="<?php the_permalink() ?>" rel="bookmark"><u><?php _e('Comments', ZEE_LANG); ?> (<?php echo get_comments_number(); ?>)</u></a>
					<p>&nbsp;</p>
				<?php
				}
				?>
				<?php endwhile; ?>
			</div>
			<?php if(function_exists('wp_page_numbers')) { wp_page_numbers(); } ?>
		<?php else : ?>
			<div class="post">
				<div>
					<p><?php _e('No matches. Please try again, or use the navigation menus to find what you search for.', ZEE_LANG); ?></p>
				</div>
			</div>

		<?php endif; ?>
	</div>
	<?php get_sidebar(); ?>

	</div>
<?php
if(strlen(trim($query)) > 0)
{
	$lenient = true;
	if(isset($_GET['match_accents']))
	{
		$lenient = false;
	}
?>
<script>
	jQuery("#searchresults").highlight('<?php echo trim(str_replace('\'', '\\\'', $query)); ?>', <?php echo $lenient; ?>);
</script>
<?php
}
get_footer();
