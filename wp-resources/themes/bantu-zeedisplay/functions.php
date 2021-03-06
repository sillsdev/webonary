<?php
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
	//if Martin Diprose
	if ((int)$user_ID == 2) {
		$upload['baseurl'] = "/wp-content/uploads/bantu";
	}

	return $upload;
}

//Load Styles and Scripts
add_action('wp_print_styles', 'themezee_stylesheets');
function themezee_stylesheets() { 
	wp_register_style('zee_stylesheet', get_stylesheet_directory_uri() . '/style.css');
	wp_enqueue_style( 'zee_stylesheet');
}
add_action('init', 'themezee_register_scripts');
function themezee_register_scripts() { 
	wp_register_script('zee_jquery-ui-min', get_template_directory_uri() .'/includes/js/jquery-ui-1.8.11.custom.min.js', array('jquery'));
	wp_register_script('zee_jquery-easing', get_template_directory_uri() .'/includes/js/jquery.easing.1.3.js', array('jquery', 'zee_jquery-ui-min'));
	wp_register_script('zee_jquery-cycle', get_template_directory_uri() .'/includes/js/jquery.cycle.all.min.js', array('jquery', 'zee_jquery-easing'));
	wp_register_script('zee_slidemenu', get_template_directory_uri() .'/includes/js/jquery.slidemenu.js', array('jquery'));
}
add_action('wp_enqueue_scripts', 'themezee_enqueue_scripts');
function themezee_enqueue_scripts() { 
	wp_enqueue_script('jquery');
	wp_enqueue_script('zee_jquery-ui-min');
	wp_enqueue_script('zee_jquery-easing');
	wp_enqueue_script('zee_jquery-cycle');
	wp_enqueue_script('zee_slidemenu');
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
add_theme_support('custom-background');
add_editor_style();

// Add Custom Header
define('HEADER_TEXTCOLOR', '');
define('HEADER_IMAGE', get_template_directory_uri() . '/images/default_header.jpg');
define('HEADER_IMAGE_WIDTH', 900);
define('HEADER_IMAGE_HEIGHT', 140);
define('NO_HEADER_TEXT', true );

function themezee_header_style() {
    ?><style type="text/css">
        #custom_header img {
			margin-top: 0px;
			width: <?php echo HEADER_IMAGE_WIDTH; ?>px;
            height: <?php echo HEADER_IMAGE_HEIGHT; ?>px;
        }
    </style><?php
}
function themezee_admin_header_style() {
    ?><style type="text/css">
        #headimg {
            width: <?php echo HEADER_IMAGE_WIDTH; ?>px;
            height: <?php echo HEADER_IMAGE_HEIGHT; ?>px;
        }
    </style><?php
}
add_theme_support('custom-header', array(
	'wp-head-callback' => 'themezee_header_style',
	'admin-head-callback' => 'themezee_admin_header_style',
));
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

register_sidebar(array('name' => 'Sidebar Blog','id' => 'sidebar-blog'));
register_sidebar(array('name' => 'Sidebar Pages','id' => 'sidebar-pages'));
register_sidebar(array('name' => 'Footer','id' => 'sidebar-footer'));

// Register Menus
register_nav_menu( 'top_navi', 'Top Navigation' );
register_nav_menu( 'main_navi', 'Main Navigation' );

// include Plugin Files
locate_template('/includes/plugins/theme_socialmedia_widget.php', true);
locate_template('/includes/plugins/theme_ads_widget.php', true);

//this function will give back all posts if user enters an empty string
function my_request_filter( $query_vars ) {
    if( isset( $_GET['s'] ) && empty( $_GET['s'] ) ) {
        $query_vars['s'] = " ";
    }
    return $query_vars;
}

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

add_filter( 'request', 'my_request_filter' );
/*==============================================================================
/* Webonary functions
/* ===========================================================================*/

define('DICTIONARY_MODE', 0);
define('BLOG_MODE', 1);

require_once(get_template_directory() . '/includes/entry.php' );
require_once(get_template_directory() . '/includes/style.php' );
require_once(get_template_directory() . '/includes/navigation.php' );
?>