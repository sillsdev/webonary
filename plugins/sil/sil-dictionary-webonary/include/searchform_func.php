<?php
/** @noinspection HtmlUnknownTarget */

use SIL\Webonary\Widgets\SearchWidget;

/**
 * A replacement for search box for dictionaries. To use, create searchform.php
 * in the theme, and make a call to this function, like so:
 */

function custom_query_vars_filter($vars) {
	$vars[] = 'match_accents';
	$vars[] = 'match_whole_words';
	$vars[] = 'semantic_domain';
	return $vars;
}
add_filter( 'query_vars', 'custom_query_vars_filter' );

function webonary_searchform($use_li = false): void
{
	if(get_option('noSearch') == 1)
		return;

	$search = new SearchWidget($use_li);
	$search->widget([], []);
}

function add_header(): void
{
	 if(!is_front_page()) {
		 $host = get_home_url(1);
?>
	<link rel="stylesheet" href="<?php echo $host; ?>/wp-content/plugins/sil-dictionary-webonary/audiolibs/css/styles.css" />
	<script src="<?php echo $host; ?>/wp-content/plugins/sil-dictionary-webonary/js/jquery.ubaplayer.js" type="text/javascript"></script>
	<script>
	jQuery(function(){
		jQuery("#ubaPlayer").ubaPlayer({
				codecs: [{name:"MP3", codec: 'audio/mpeg'}]
			});
         });
     </script>
<?php
	 }
}
add_action('wp_head', 'add_header');


/**
 * @throws Exception
 */
function add_footer(): void
{
	global $post, $wpdb;

	if (get_current_blog_id() < 2)
		return;

	// for new themes this is implemented through a widget
	$template = wp_get_theme()->get_template();
	if ($template == 'bootscore')
		return;

	$post_slug = is_null($post) ? '' : $post->post_name;
	if(is_front_page() || $post_slug == 'browse')
	{
		if(get_option('noSearch') != 1)
		{
			$letter = 'frontpage';
			if(isset($_GET['letter']))
			{
				$letter = $_GET['letter'];
			}

			$sql = "SELECT post_title FROM $wpdb->posts WHERE post_content LIKE '%[vernacularalphabet]%'";

			$browse_title = $wpdb->get_var($sql);

			$alphabetDisplay = Webonary_ShortCodes::VernacularAlphabet($letter);

			if(strlen($alphabetDisplay) > 0)
			{
			?>
			<div style="padding-left: 20px; padding-right: 20px; padding-bottom: 10px;">
				<div style="width: 100%; height: 12px; border-bottom: 1px solid black; text-align: center">
				  <span style="font-size: 16px; background-color: #FFFFFF; padding: 0 10px;">
				    <?php _e($browse_title); ?>
				  </span>
				</div>
				<?php echo $alphabetDisplay; ?>
			</div>

			<?php
			}
		}
		if ( get_option( 'publicationStatus' ) && $post_slug != 'browse' ) {

			$publicationStatus = get_option( 'publicationStatus' );

			if ( $publicationStatus > 0 ) {

				echo Webonary_Published_Widget::getDictStageFlex( $publicationStatus );
			}
		}
	}
	?>
	<div id="ubaPlayer"></div>
<?php
}
add_action('wp_footer', 'add_footer');
