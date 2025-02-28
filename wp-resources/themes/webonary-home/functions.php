<?php /** @noinspection PhpNoReturnAttributeCanBeAddedInspection */

// Set Content Width
if ( ! isset( $content_width ) )
  $content_width = 480;

function isMobile(): bool
{
	$useragent=$_SERVER['HTTP_USER_AGENT'];
	$isMobile = false;
	if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series([46])0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent) || preg_match('#1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br[ev]w|bumb|bw-[nu]|c55/|capi|ccwa|cdm-|cell|chtm|cldc|cmd-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc-s|devi|dica|dmob|do[cp]o|ds(12|-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly[-_]|g1 u|g560|gene|gf-5|g-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd-[mpt]|hei-|hi(pt|ta)|hp( i|ip)|hs-c|ht(c[- _agpst]|tp)|hu(aw|tc)|i-(20|go|ma)|i230|iac[ -/]|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja[tv]a|jbro|jemu|jigs|kddi|keji|kgt[ /]|klon|kpt |kwc-|kyo[ck]|le(no|xi)|lg( g|/[klu]|50|54|-[a-w])|libw|lynx|m1-w|m3ga|m50/|ma(te|ui|xo)|mc(01|21|ca)|m-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t[- ov]|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30[02]|n50[025]|n7(0[01]|10)|ne([cm]-|on|tf|wf|wg|wt)|nok[6i]|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan[adt]|pdxg|pg(13|-([1-8]|c))|phil|pire|pl(ay|uc)|pn-2|po(ck|rt|se)|prox|psio|pt-g|qa-a|qc(07|12|21|32|60|-[2-7]|i-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55/|sa(ge|ma|mm|ms|ny|va)|sc(01|h-|oo|p-)|sdk/|se(c[-01]|47|mc|nd|ri)|sgh-|shar|sie[-m]|sk-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h-|v-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl-|tdg-|tel[im]|tim-|t-mo|to(pl|sh)|ts(70|m-|m3|m5)|tx-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c[- ]|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas-|your|zeto|zte-#i',substr($useragent,0,4)))
	{
		$isMobile = true;
		//header('Location: http://detectmobilebrowser.com/mobile');
	}
	return $isMobile;
}

/*==================================== THEME SETUP ====================================*/

// Load default style.css and Javascripts
add_action('wp_enqueue_scripts', 'themezee_enqueue_scripts');

if ( ! function_exists( 'themezee_enqueue_scripts' ) ):
function themezee_enqueue_scripts(): void
{

  // Register and Enqueue Stylesheet
  wp_register_style('zee_stylesheet', get_stylesheet_directory_uri() . '/style.css?v=1.6');
  wp_enqueue_style( 'zee_stylesheet');

  wp_register_style('responsive_menu_stylesheet', get_stylesheet_directory_uri() . '/includes/css/responsive-menu.css?v=1');
  wp_enqueue_style('responsive_menu_stylesheet');

  // Enqueue jQuery Framework
  wp_enqueue_script('jquery');

  // Register and enqueue the Malsup Cycle Plugin
  wp_register_script('zee_jquery-cycle', get_template_directory_uri() .'/includes/js/jquery.cycle.all.min.js', array('jquery'));
  wp_enqueue_script('zee_jquery-cycle');

  wp_register_script('responsiveMenu', get_template_directory_uri() .'/includes/js/responsive-menu.js?v=1.1', array('jquery'));
  wp_enqueue_script( 'responsiveMenu' );
}
endif;


// Load comment-reply.js if comment form is loaded and threaded comments activated
add_action( 'comment_form_before', 'themezee_enqueue_comment_reply' );

function themezee_enqueue_comment_reply(): void
{
  if( get_option( 'thread_comments' ) ) {
    wp_enqueue_script( 'comment-reply' );
  }
}


// Setup Function: Registers support for various WordPress features
add_action( 'after_setup_theme', 'themezee_setup' );

if ( ! function_exists( 'themezee_setup' ) ):
function themezee_setup(): void
{

  // init Localization
  load_theme_textdomain('themezee_lang', get_template_directory() . '/includes/lang' );

  // Add Theme Support
  add_theme_support('post-thumbnails');
  add_theme_support('automatic-feed-links');
  add_editor_style();

  // Add Custom Background
  add_theme_support('custom-background', array('default-color' => 'efefef'));

  // Add Custom Header
  add_theme_support('custom-header', array(
    'default-image' => get_template_directory_uri() . '/images/default_header.jpg',
    'header-text' => false,
    'width'  => 900,
    'height' => 140,
    'flex-height' => true));

  // Register Navigation Menus
  register_nav_menu( 'top_navi', __('Top Navigation', 'themezee_lang') );
  register_nav_menu( 'main_navi', __('Main Navigation', 'themezee_lang') );
}
endif;

// Register Sidebars
add_action( 'widgets_init', 'themezee_register_sidebars' );

if ( ! function_exists( 'themezee_register_sidebars' ) ):
function themezee_register_sidebars(): void
{

  // Register Sidebars
  register_sidebar(array('name' => __('Sidebar Blog', 'themezee_lang'), 'id' => 'sidebar-blog'));
  register_sidebar(array('name' => __('Sidebar Pages', 'themezee_lang'), 'id' => 'sidebar-pages'));

  // Register Footer Bars
  register_sidebar(array('name' => __('Footer', 'themezee_lang'), 'id' => 'sidebar-footer'));
}
endif;


/*==================================== INCLUDE FILES ====================================*/

// Includes all files needed for theme options, custom JS/CSS and Widgets
add_action( 'after_setup_theme', 'themezee_include_files' );

if ( ! function_exists( 'themezee_include_files' ) ):
function themezee_include_files(): void
{

  // include Admin Files
  locate_template('/includes/admin/theme-functions.php', true);
  locate_template('/includes/admin/theme-admin.php', true);

  // include custom Javascript and custom CSS Handler files
  locate_template('/includes/js/jscript.php', true);
  locate_template('/includes/css/csshandler.php', true);

  // include Widget Files
  locate_template('/includes/widgets/theme-widget-ads.php', true);
  locate_template('/includes/widgets/theme-widget-socialmedia.php', true);
}
endif;


/*==================================== THEME FUNCTIONS ====================================*/

// Creates a better title element text for output in the head section
add_filter( 'wp_title', 'themezee_wp_title', 10, 2 );

function themezee_wp_title( $title, $sep = '' ) {
  global $paged, $page;

  if ( is_feed() )
    return $title;

  // Add the site name.
  $title .= get_bloginfo( 'name' );

  // Add the site description for the home/front page.
  $site_description = get_bloginfo( 'description', 'display' );
  if ( $site_description && ( is_home() || is_front_page() ) )
    $title = "$title $sep $site_description";

  // Add a page number if necessary.
  if ( $paged >= 2 || $page >= 2 )
    $title = "$title $sep " . sprintf( __( 'Page %s', 'themezee' ), max( $paged, $page ) );

  return $title;
}


// Add Default Menu Fallback Function
function themezee_default_menu(): void
{
  echo '<ul id="nav" class="menu">'. wp_list_pages('title_li=&echo=0') .'</ul>';
}


// Display Credit Link Function
function themezee_credit_link(): void
{ ?>
  <a href="https://themezee.com/"><?php _e('Wordpress Theme by ThemeZee', 'themezee_lang'); ?></a>
<?php
}


// Change Excerpt Length
add_filter('excerpt_length', 'themezee_excerpt_length');
function themezee_excerpt_length($length): int
{
    return 25;
}


// Change Excerpt More
add_filter('excerpt_more', 'themezee_excerpt_more');
function themezee_excerpt_more($more): string
{
    return '';
}


// Add Postmeta Data
add_action( 'themezee_display_postmeta_index', 'themezee_postmeta_content' );
add_action( 'themezee_display_postmeta_single', 'themezee_postmeta_content' );

function themezee_postmeta_content(): void
{ ?>
  <span class="date"><a href="<?php the_permalink() ?>"><?php the_time(get_option('date_format')); ?></a></span>
  <span class="author"><?php the_author_posts_link(); ?> </span>
  <span class="comment"><?php comments_popup_link( __('No comments', 'themezee_lang'),__('One comment','themezee_lang'),__('% comments','themezee_lang') ); ?></span>
<?php
  edit_post_link(__( 'Edit', 'themezee_lang' ), ' | ');
}


// Add Postinfo Data
add_action( 'themezee_display_postinfo_index', 'themezee_postinfo_content' );
add_action( 'themezee_display_postinfo_single', 'themezee_postinfo_content' );

function themezee_postinfo_content(): void
{ ?>
  <span class="folder"><?php the_category(', '); ?> </span>
<?php if (get_the_tags())
  echo'<span class="tag">'; the_tags(''); echo '</span>';
}

// As of 3.1.10, Customizr doesn't output an html5 form.
add_theme_support( 'html5', array( 'search-form' ) );

function wpb_remove_schedule_delete(): void
{
	remove_action( 'wp_scheduled_delete', 'wp_scheduled_delete' );
}
add_action( 'init', 'wpb_remove_schedule_delete' );

function getPublishedSitesCount($atts): ?string
{
	global $wpdb;

	$sql = "SELECT COUNT(link_url) AS publishedSitesCount " .
			" FROM wp_links " .
			" INNER JOIN wp_term_relationships ON wp_links.link_id = wp_term_relationships.object_id " .
			" INNER JOIN wp_terms ON wp_terms.term_id = wp_term_relationships.term_taxonomy_id " .
			" WHERE wp_terms.slug = 'available-dictionaries'";

	return $wpdb->get_var($sql);
}
add_shortcode( 'publishedSitesCount', 'getPublishedSitesCount' );

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


/*
add_filter('wp_nav_menu_items', 'add_search_form_to_menu', 10, 2);
function add_search_form_to_menu($items, $args) {

 // If this isn't the main navbar menu, do nothing
 if( !($args->theme_location == 'top_navi') )
 return $items;

 // On main menu: put styling around search and append it to the menu items
 return $items . '<li class="my-nav-menu-search" style="background-color: #FFFFFF;">' . get_search_form(false) . '</li>';
}
*/


function webonary_home_enqueue_jquery(): void
{

	wp_deregister_script( 'jquery-core' );
	wp_register_script( 'jquery-core', 'https://code.jquery.com/jquery-3.5.1.min.js', [], '3.5.1' );

	wp_deregister_script( 'jquery-migrate' );
	wp_register_script( 'jquery-migrate', 'https://code.jquery.com/jquery-migrate-3.3.2.min.js', ['jquery-core'], '3.3.2' );
}

// load jquery first
add_action('wp_enqueue_scripts', 'webonary_home_enqueue_jquery', 5);
add_action('login_enqueue_scripts', 'webonary_home_enqueue_jquery', 5);

function full_width_body_classes($classes) {
	$classes[] = 'full-width';
	return $classes;
}

function ajax_display_sites(): void
{
	include_once 'includes/src/WebonaryHome_Ajax.php';

	header('Content-Type: application/json');
	$data = ['data' => WebonaryHome_Ajax::GetAllSites(false)];
	echo json_encode($data);
	exit();
}
add_action('wp_ajax_getAjaxDisplaySites', 'ajax_display_sites');

function ajax_grammar_sites(): void
{
	include_once 'includes/src/WebonaryHome_Ajax.php';

	header('Content-Type: application/json');
	$data = ['data' => WebonaryHome_Ajax::GetGrammarSites(false)];
	echo json_encode($data);
	exit();
}
add_action('wp_ajax_nopriv_getAjaxGrammarSites', 'ajax_grammar_sites');
