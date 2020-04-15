<?php
load_theme_textdomain('dictrans');
register_sidebar(array(
        'name' => __( 'Search Bar Popups', 'dictrans' ),
		'id' => 'topsearchbar',
		'description' => __( 'If a widget placed here has links, those links will appear above the search field on the main screen.', 'searchform' ),
        'after_widget' => '',
        'before_title' => '<h4>',
        'after_title' => '</h4>',
    ));
    
if ( function_exists('register_sidebar') ){
    register_sidebar(array(
        'name' => __( 'Left Side Bar', 'dictrans' ),
        'id' => 'leftsidebar',
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '<h4>',
        'after_title' => '</h4>',
    ));
}
register_sidebar(array(
        'name' => __( 'Right Side Bar', 'dictrans' ),
        'id' => 'rightsidebar',
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '<h4>',
        'after_title' => '</h4>',
    ));


//this function is used to exclude certain pages from the sidebar, like copyright-page
function get_id_by_slug($page_slug) {
    $page = get_page_by_path($page_slug);
    if ($page) {
        return $page->ID;
    } else {
        return null;
    }
}
/*
 * Use the stylesheet that came with the XHMTL file. get_stylesheet_directory()
 * and get_stylesheet_directory_uri() are returning the theme directory for me.
 * That's where style.css is, so that makes sense.
 */

// @todo: When using the .css generated from FieldWorks, we get what the user
// wants. However, the background color is white. And the Yi script becomes too
// big.

// @todo: Should the .css be moved into the same directory as style.css? Or
// should it be placed in one of the plugin directories?

// @todo: Make the importer put file put the .css where it should go. Using
// WP_PLUGIN_URL looks like standard practice.

$stylesheet = 'imported-with-xhtml.css';
//$stylesheet = WP_PLUGIN_URL . '/Multilingual-Dictionary.css';
if ( file_exists( get_stylesheet_directory() . '/' . $stylesheet ) ) {
	wp_register_style( 'Multilingual-Dictionary', get_stylesheet_directory_uri() . "/" . $stylesheet );
	wp_enqueue_style( 'Multilingual-Dictionary' );
}

function remove_title($input) {
  return preg_replace_callback('#\stitle=["|\'].*["|\']#',
    create_function(
      '$matches',
      'return "";'
      ),
      $input
    );
  }
add_filter('wp_list_pages','remove_title');

function qtranslate_edit_taxonomies(){
   $args=array(
      'public' => true ,
      '_builtin' => false
   );
   $output = 'object'; // or objects
   $operator = 'and'; // 'and' or 'or'

   $taxonomies = get_taxonomies($args,$output,$operator);

   if  ($taxonomies) {
     foreach ($taxonomies  as $taxonomy ) {
         add_action( $taxonomy->name.'_add_form', 'qtrans_modifyTermFormFor');
         add_action( $taxonomy->name.'_edit_form', 'qtrans_modifyTermFormFor');        
      
     }
   }

}
add_action('admin_init', 'qtranslate_edit_taxonomies');

/***************************************************************
* Function qtranslate_next_previous_fix
* Ensure that the URL for next_posts_link & previous_posts_link work with qTranslate
***************************************************************/

add_filter('get_pagenum_link', 'qtranslate_next_previous_fix');

function qtranslate_next_previous_fix($url) {
   if (function_exists('qtrans_init'))
   {
   	return qtrans_convertURL($url);
   }
}

/***************************************************************
* Function qtranslate_single_next_previous_fix
* Ensure that the URL for next_post_link & previous_post_link work with qTranslate
***************************************************************/

add_filter('next_post_link', 'qtranslate_single_next_previous_fix');
add_filter('previous_post_link', 'qtranslate_single_next_previous_fix');

function qtranslate_single_next_previous_fix($url) {
   $just_url = preg_match("/href=\"([^\"]*)\"/", $url, $matches);
   return str_replace($matches[1], qtrans_convertURL($matches[1]), $url);
}

function qtrans_getLanguageLinks($style='', $id='') {
 if (function_exists('qtrans_init'))
 {
	global $q_config;
	if($style=='') $style='text';
	if(is_bool($style)&&$style) $style='image';
	if(is_404()) $url = get_option('home'); else $url = '';
	if($id=='') $id = 'qtranslate';
	$id .= '-chooser';
	switch($style) {
		case 'image':
		case 'text':
		case 'dropdown':
			echo '<ul class="qtrans_language_chooser" id="'.$id.'">';
			foreach(qtrans_getSortedLanguages() as $language) {
				echo '<li';
				if($language == $q_config['language'])
					echo ' class="active"';
				echo '><a href="'.qtrans_convertURL($url, $language).'"';
				// set hreflang
				echo ' hreflang="'.$language.'" title="'.$q_config['language_name'][$language].'"';
				if($language == "ii")
				{
					echo ' class=nuosu';
				}
				echo '><span';
				echo '>'.$q_config['language_name'][$language].'</span></a></li>';
			}
			echo "</ul><div class=\"qtrans_widget_end\"></div>";
	}
 }
}

?>