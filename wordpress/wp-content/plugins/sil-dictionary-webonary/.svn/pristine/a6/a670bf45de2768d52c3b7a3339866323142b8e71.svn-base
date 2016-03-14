<?php
/**
 * A replacement for search box for dictionaries. To use, create searchform.php
 * in the theme, and make a call to this function, like so:
 */
function searchform_init() {
	/*
	 * Load the translated strings for the plugin.
	 */
    load_plugin_textdomain('sil_dictionary', false, dirname(plugin_basename(__FILE__ )).'/lang/');
}

function webonary_searchform() {
	global $wpdb;
?>
		 <form name="searchform" id="searchform" method="get" action="<?php bloginfo('url'); ?>">
			<div class="normalSearch">
				<!-- Search Bar Popups --> <?php !dynamic_sidebar( 'topsearchbar' ); ?><!-- end Search Bar Popups -->
				<!-- search text box -->
				<input type="text" name="s" id="s" value="<?php the_search_query(); ?>" size=40>
	
				<!-- I'm not sure why qtrans_getLanguage() is here. It doesn't seem to do anything. -->
				<?php if (function_exists('qtrans_getLanguage')) {?>
					<input type="hidden" id="lang" name="lang" value="<?php echo qtrans_getLanguage(); ?>"/>
				<?php }?>
	
				<!-- search button -->
				<input type="submit" id="searchsubmit" name="search" value="<?php _e('Search', 'sil_dictionary'); ?>" />
				<br>
				<?php
				$key = $_POST['key'];
				if(!isset($_POST['key']))
				{
					$key = $_GET['key'];
				}
	
				$catalog_terms = get_terms('sil_writing_systems');
	
				/*
				 * Set up language options. The first option is for all
				 * languages. Then the list is retrieved.
				 */
				if ($catalog_terms) {
					?>
					<!-- If you need to control the width of the dropdown, use the
					class webonary_searchform_language_select in your theme .css -->
					<select name="key" class="webonary_searchform_language_select">
					<option value="">
						<?php _e('All Languages','sil_dictionary'); ?>
					</option>
					<?php
					foreach ($catalog_terms as $catalog_term)
					{ ?>
						<option value="<?php echo $catalog_term->slug; ?>"
							<?php if($key == $catalog_term->slug) {?>selected<?php }?>>
							<?php echo $catalog_term->name; ?>
						</option>
						<?php
					}
					?>
					</select>
					<br>
					<?php
				}
	
				/*
				 * Set up the Parts of Speech
				 */
				$parts_of_speech = get_terms('sil_parts_of_speech');
				
				if($parts_of_speech)
				{
					wp_dropdown_categories("show_option_none=" .
						__('All Parts of Speech','sil_dictionary') .
						"&show_count=1&selected=" . $_GET['tax'] .
						"&orderby=name&echo=1&name=tax&taxonomy=sil_parts_of_speech");
				}
				?>
			</div>
		</form>
		<?php
		if(strlen(trim($_GET['s'])) > 0)
		{
			//$sem_domains = get_terms( 'sil_semantic_domains', 'name__like=' .  trim($_GET['s']) .'');
			$query = "SELECT t.*, tt.* FROM " . $wpdb->terms . " AS t INNER JOIN " . $wpdb->term_taxonomy . " AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('sil_semantic_domains') AND t.name LIKE '%" . trim($_GET['s']) . "%' AND tt.count > 0 GROUP BY t.name ORDER BY t.name ASC";
    		$sem_domains = $wpdb->get_results( $query );
    		
			if(count($sem_domains) > 0 && count($sem_domains) <= 10)
			{
				echo "<p>&nbsp;</p>";
				echo "<strong>";
				 _e('Found in Semantic Domains:', 'sil_dictionary');
				echo "</strong>";
				echo "<ul>";
				foreach ($sem_domains as $sem_domain ) {
				  echo '<li><a href="?s=&partialsearch=1&tax=' . $sem_domain->term_id . '">'. $sem_domain->description . '</a></li>';
				}
				echo "</ul>";
			}
		}
		?>
<?php
}

add_action('init', 'searchform_init');

function add_header()
{
	 if(!is_front_page()) {
?>
	<link rel="stylesheet" href="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/sil-dictionary-webonary/audiolibs/css/styles.css" />
	<script src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/sil-dictionary-webonary/js/jquery.ubaplayer.js" type="text/javascript"></script>
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

function getDictStageImage($publicationStatus, $language)
{
	if($language == "en")
	{
		$language = "";
	}
	$DictStage = "/wp-content/plugins/sil-dictionary-webonary/images/status/DictStage" . $publicationStatus . $language . ".png";

	if(file_exists(ABSPATH . $DictStage))
	{
		echo $DictStage;
	}
	else
	{
		getDictStageImage($publicationStatus, "");
	}
}

function add_footer()
{
?>
	<?php
	if(get_option('publicationStatus'))
	{
		$publicationStatus = get_option('publicationStatus');
		if(is_front_page() && $publicationStatus > 0) {

			$language = "";
			if (function_exists('qtranxf_getLanguage')) {
				$language = qtranxf_getLanguage();
			}
		?>
		
		<div align=center><img src="<?php getDictStageImage($publicationStatus, $language); ?>" style="padding: 5px; max-width: 100%;"></div>
	<?php
		}
	}
	?>
	<div id="ubaPlayer"></div>
<?php
}

add_action('wp_footer', 'add_footer');
?>