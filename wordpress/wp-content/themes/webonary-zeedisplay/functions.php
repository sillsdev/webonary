<?php
//Wordpress formats Apostrophe into Right Single Quote, this undoes the change
//remove_filter ('the_content', 'wptexturize');

if (function_exists('qtrans_init'))
{
	/*==============================================================================
	 /*qTrans functions
	 /*=============================================================================*/
	load_theme_textdomain('dictrans');

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
					echo '><span';
					echo '>'.$q_config['language_name'][$language].'</span></a></li>';
				}
				echo "</ul><div class=\"qtrans_widget_end\"></div>";
		}
	}
}

/*==============================================================================
 /* zeeDisplay functions
 /* ===========================================================================*/

// Theme Name
define('THEME_NAME', 'zeeDisplay');
define('THEME_INFO', 'http://themezee.com/zeeDisplay');

//Content Width
$content_width = 480;

add_filter('upload_dir', 'ml_media_upload_dir');

/**
 * Changes the upload directory to what we would like, instead of what WordPress likes.
 *
 *
 */
function ml_media_upload_dir($upload) {
	global $user_ID;

	return $upload;
}

//Load Styles and Scripts
//add_action('wp_print_styles', 'themezee_stylesheets');
add_action('wp_enqueue_scripts', 'themezee_stylesheets', 1);
add_action('init', 'themezee_register_scripts');
function themezee_register_scripts() {

	 wp_register_script('zee_jquery-ui-min', get_template_directory_uri() .'/includes/js/jquery-ui-1.8.11.custom.min.js', array('jquery'));
	/*
	 wp_register_script('zee_jquery-easing', get_template_directory_uri() .'/includes/js/jquery.easing.1.3.js', array('jquery', 'zee_jquery-ui-min'));
	 wp_register_script('zee_jquery-cycle', get_template_directory_uri() .'/includes/js/jquery.cycle.all.min.js', array('jquery', 'zee_jquery-easing'));
	 */
	wp_register_script('responsiveMenu', get_template_directory_uri() .'/includes/js/responsive-menu.js?v=1', array('jquery'));
	wp_register_script('zee_slidemenu', get_template_directory_uri() .'/includes/js/jquery.slidemenu.js', array('jquery'));
	//wp_register_script('highlight', get_template_directory_uri() .'/includes/js/jquery-highlight-min-old.js', array('jquery'));
	wp_register_script('highlight', get_template_directory_uri() .'/includes/js/jquery-highlight1.js?v=1.1', array('jquery'));
}
add_action('wp_enqueue_scripts', 'themezee_enqueue_scripts');

function themezee_stylesheets() {
	wp_register_style('zee_stylesheet', get_stylesheet_directory_uri() . '/style.css?v=1.5.2');
	wp_enqueue_style( 'zee_stylesheet');

	wp_register_style('repsonive_menu_stylesheet', get_stylesheet_directory_uri() . '/includes/styles/responsive-menu.css?v=1');
	wp_enqueue_style( 'repsonive_menu_stylesheet');

	wp_register_style('custom_stylesheet', '/files/custom.css?time=' . date("U"));
	wp_enqueue_style( 'custom_stylesheet');
}
function themezee_enqueue_scripts() {

	 wp_enqueue_script('jquery');
	 /*
	 wp_enqueue_script('zee_jquery-ui-min');
	 wp_enqueue_script('zee_jquery-easing');
	 wp_enqueue_script('zee_jquery-cycle');
	 */
    wp_enqueue_script( 'responsiveMenu' );

	wp_enqueue_script('zee_slidemenu');
	wp_enqueue_script('highlight');
}
locate_template('/includes/js/jscript.php', true);
locate_template('/includes/styles/custom-css.php', true);

// init Localization
define('ZEE_LANG', 'zeeDisplay');
load_theme_textdomain(ZEE_LANG, TEMPLATEPATH . '/includes/lang');

// include Admin Files
locate_template('/includes/admin/theme-functions.php', true);
locate_template('/includes/admin/theme-settings.php', true);
locate_template('/includes/admin/theme-admin.php', true);

// Add Theme Functions
add_theme_support('post-thumbnails');
add_theme_support('automatic-feed-links');
add_custom_background();
add_editor_style();

// Add Custom Header
define('HEADER_TEXTCOLOR', '');
define('HEADER_IMAGE', get_template_directory_uri() . '/images/default_header.jpg');
define('HEADER_IMAGE_WIDTH', 900);
//define('HEADER_IMAGE_HEIGHT', 140);
define('NO_HEADER_TEXT', true );

function themezee_header_style() {
	if(!isMobile())
	{
		?>
<style type="text/css">
#custom_header img {
	margin-top: 0px;
	width: <?php echo HEADER_IMAGE_WIDTH; ?> px;
	height: <?php echo HEADER_IMAGE_HEIGHT; ?> px;
}
</style>
	<?php
	}
}
function themezee_admin_header_style() {
	?>
<style type="text/css">
#headimg {
	width: <?php echo HEADER_IMAGE_WIDTH; ?> px;
	height: <?php echo HEADER_IMAGE_HEIGHT;
	?>
	px;
}
</style>
	<?php
}
add_custom_image_header('themezee_header_style', 'themezee_admin_header_style');

// Register Sidebars
register_sidebar(array(
		'name' => __( 'Search Bar Popups', 'dictrans' ),
		'id' => 'topsearchbar',
		'description' => __( 'If a widget placed here has links, those links will appear above the search field on the main screen.', 'searchform' ),
		'before_widget' => '<ul>',
		'after_widget'  => "</ul>",
		'before_title' => '<h4>',
		'after_title' => '</h4>',
));


//register_sidebar(array('name' => 'Sidebar Blog','id' => 'sidebar-blog'));
register_sidebar(array('name' => 'Sidebar Pages','id' => 'sidebar-pages'));
register_sidebar(array('name' => 'Header','id' => 'sidebar-header'));
register_sidebar(array('name' => 'Footer','id' => 'sidebar-footer'));

// Register Menus
register_nav_menu( 'top_navi', 'Top Navigation' );
//register_nav_menu( 'main_navi', 'Main Navigation' );

// include Plugin Files
locate_template('/includes/plugins/theme_socialmedia_widget.php', true);
locate_template('/includes/plugins/theme_ads_widget.php', true);

// Functions for correct html5 Validation
function themezee_html5_gallery($content)
{
	return str_replace('[gallery', '[gallery itemtag="div" icontag="span" captiontag="p"', $content);
}
add_filter('the_content', 'themezee_html5_gallery');
add_filter('gallery_style', create_function('$a', 'return preg_replace("%<style type=\'text/css\'>(.*?)</style>%s", "", $a);'));

function themezee_html5_embed($return, $data, $url)
{
	$search = '|></embed>|is';
	$replace = ' />';
	return preg_replace( $search, $replace, $return );
}
add_filter( 'oembed_dataparse', 'themezee_html5_embed', 10, 3);

function themezee_html5_elements($content)
{
	$content = str_replace('<acronym', '<abbr', $content);
	$content = str_replace('</acronym', '</abbr', $content);
	$content = str_replace('<big', '<span class="big_tag"', $content);
	$content = str_replace('</big', '</span', $content);
	$content = str_replace('<tt', '<span class="tt_tag"', $content);
	$content = str_replace('</tt', '</span', $content);
	return $content;
}
add_filter('the_content', 'themezee_html5_elements');
add_filter('comment_text', 'themezee_html5_elements');

/*==============================================================================
 /* Webonary functions
 /* ===========================================================================*/

define('DICTIONARY_MODE', 0);
define('BLOG_MODE', 1);

require_once(get_template_directory() . '/includes/entry.php' );
require_once(get_template_directory() . '/includes/style.php' );
require_once(get_template_directory() . '/includes/navigation.php' );

function isMobile()
{
	return false;
}

function custom_upload_mimes ( $existing_mimes=array() ) {

	// add your ext => mime to the array
	$existing_mimes['apk'] = 'application/vnd.android.package-archive';

	$existing_mimes['woff'] = 'application/x-font-woff';
	$existing_mimes['epub|mobi'] = 'application/octet-stream';

	// add as many as you like

	// and return the new full result

	return $existing_mimes;

}
add_filter('upload_mimes', 'custom_upload_mimes');

//register menu shortcode
add_shortcode('menu', 'shortcode_menu');
function shortcode_menu($args ) {
	//don't echo the ouput so we can return it
	$args['echo']=false;
	//in case menu isn't found display a message
	$args['fallback_cb']='shortcode_menu_fallback';
	//check if showing a submenu, if so make sure everything is setup to do so
	if (isset($args['show_submenu']) && !empty($args['show_submenu'])) {
		if (!isset($args['depth'])) {
			$args['depth']=1;
		}
		$args['walker']=new Sub_Menu_Walker_Nav_Menu();
	}
	$menu=wp_nav_menu( $args );
	return $menu;
}
//message to display if menu isn't found
function shortcode_menu_fallback($args ) {return 'No menu selected.';}

function my_password_form() {
	global $post;
	$label = 'pwbox-'.( empty( $post->ID ) ? rand() : $post->ID );
	$o = '<form action="' . esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ) . '" method="post">
    ' . __( "To view this protected post, enter the password below:" ) .
    ' <p></p> ' .
	__("If you do not have a password, you may request it using the <a href=\"/help/contact-us/\">contact form</a>.") . '
    <label for="' . $label . '">' . __( "Password:" ) . ' </label><input name="post_password" id="' . $label . '" type="password" size="20" maxlength="20" /><input type="submit" name="Submit" value="' . esc_attr__( "Submit" ) . '" />
    </form>
    ';
	return $o;
}
add_filter( 'the_password_form', 'my_password_form' );

//special walker_nav_menu class to only display submenu
//depth must be greater than 0
//show_submenu specifies submenu to display
class Sub_Menu_Walker_Nav_Menu extends Walker_Nav_Menu {
	function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output ) {
		if ( !$element )
		return;

		$id_field = $this->db_fields['id'];

		$displaythiselement=$depth!=0;
		if ($displaythiselement) {
			//display this element
			if ( is_array( $args[0] ) )
			$args[0]['has_children'] = ! empty( $children_elements[$element->$id_field] );
			$cb_args = array_merge( array(&$output, $element, $depth), $args);
			call_user_func_array(array(&$this, 'start_el'), $cb_args);
		}

		$id = $element->$id_field;
		if ( is_array( $args[0] ) ){
			$show_submenu=$args[0]['show_submenu'];
		}else
		$show_submenu=$args[0]->show_submenu;

		$url = explode("/", $element->url);
		if(end($url) == "" || substr(end($url), 0, 1) == "?")
		{
			array_pop($url);
		}
		// descend only when the depth is right and there are childrens for this element
		$link = explode("?", end($url));
		if ( ($max_depth == 0 || $max_depth >= $depth+1 ) && isset( $children_elements[$id]) && $link[0]==$show_submenu) {

			foreach( $children_elements[ $id ] as $child ){

				if ( !isset($newlevel) ) {
					$newlevel = true;
					//start the child delimiter
					$cb_args = array_merge( array(&$output, $depth), $args);
					call_user_func_array(array(&$this, 'start_lvl'), $cb_args);
				}
				$this->display_element( $child, $children_elements, $max_depth, $depth + 1, $args, $output );
			}
			unset( $children_elements[ $id ] );
		}

		if ( isset($newlevel) && $newlevel ){
			//end the child delimiter
			$cb_args = array_merge( array(&$output, $depth), $args);
			call_user_func_array(array(&$this, 'end_lvl'), $cb_args);
		}

		if ($displaythiselement) {
			//end this element
			$cb_args = array_merge( array(&$output, $element, $depth), $args);
			call_user_func_array(array(&$this, 'end_el'), $cb_args);
		}
	}
}

function crunchify_disable_comment_url($fields) {
	unset($fields['url']);
	return $fields;
}
add_filter('comment_form_default_fields','crunchify_disable_comment_url');
?>