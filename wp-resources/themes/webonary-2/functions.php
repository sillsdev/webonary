<?php

if (!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

const WEBONARY_THEME_DOMAIN = 'webonary2';
$webonary2_class_path = get_template_directory() . DS . 'classes';


/**
 * The auto-loader for the theme
 *
 * @param $class_name
 *
 * @return WP_Error|null
 */
function webonary2_autoloader($class_name): ?WP_Error
{
	global $webonary2_class_path;

	$pos = strpos($class_name, 'Webonary2_');

	// class name must begin with "Webonary2_"
	if ($pos === false || $pos != 0)
		return null;

	// check for an interface file
	$pos = strpos($class_name, 'Webonary2_Interface_');

	if ($pos !== false)
		$class_file = 'interface' . DS . substr($class_name, 19) . '.php';
	else
		$class_file = $class_name . '.php';

	$success = include_once $webonary2_class_path . DS . $class_file;


	if ($success === false)
		return new WP_Error('Failed', 'Not able to include ' . $class_name);

	return null;
}
spl_autoload_register('webonary2_autoloader');

function webonary2_setup()
{
	add_action('wp_enqueue_scripts', array('Webonary2_Customize', 'EnqueueScripts'), 5);
	// add_action('login_enqueue_scripts', array('Webonary2_Customize', 'UnqueueJquery'), 5);

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 1568, 9999 );

	register_nav_menus(
		array(
			'primary' => esc_html__('Top Menu', WEBONARY_THEME_DOMAIN),
			'footer'  => esc_html__('Footer Menu', WEBONARY_THEME_DOMAIN),
		)
	);

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support('html5');

	/*
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	$logo_width  = 300;
	$logo_height = 100;

	add_theme_support(
		'custom-logo',
		array(
			'height'               => $logo_height,
			'width'                => $logo_width,
			'flex-width'           => true,
			'flex-height'          => true,
			'unlink-homepage-logo' => true,
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	// Add support for Block Styles.
	add_theme_support( 'wp-block-styles' );

	// Add support for full and wide align images.
	add_theme_support( 'align-wide' );

	// Add support for editor styles.
	add_theme_support( 'editor-styles' );

	$editor_stylesheet_path = './css/style.css';

	// Note, the is_IE global variable is defined by WordPress and is used
	// to detect if the current browser is internet explorer.
	global $is_IE;
	if ( $is_IE ) {
		$editor_stylesheet_path = './assets/css/ie-editor.css';
	}

	// Enqueue editor styles.
	add_editor_style( $editor_stylesheet_path );
}
add_action('after_setup_theme', 'webonary2_setup');

function webonary2_widgets_init()
{
	register_sidebar([
		'name' => 'Sidebar Front Page',
		'id' => 'sidebar-front',
		'before_widget' => '<div>',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="rounded">',
		'after_title'   => '</h2>'
	]);

	register_sidebar([
		'name' => 'Sidebar Pages',
		'id' => 'sidebar-pages'
	]);

	register_sidebar([
		'name' => 'Sidebar Posts',
		'id' => 'sidebar-posts',
		'before_widget' => '<div>',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="rounded">',
		'after_title'   => '</h2>'
	]);
}
add_action( 'widgets_init', 'webonary2_widgets_init' );

function webonary2_published_site_count(): ?string
{
	global $wpdb;

	$sql = <<<SQL
SELECT COUNT(link_url) AS publishedSitesCount
FROM wp_links
  INNER JOIN wp_term_relationships ON wp_links.link_id = wp_term_relationships.object_id
  INNER JOIN wp_terms ON wp_terms.term_id = wp_term_relationships.term_taxonomy_id
WHERE wp_terms.slug = 'available-dictionaries'
SQL;
	return $wpdb->get_var($sql);
}
add_shortcode('publishedSitesCount', 'webonary2_published_site_count');

function webonary2_custom_header_setup() {
	$args = array(
		'default-image'      => home_url() . 'files/img/default-image.jpg',
		'default-text-color' => '000',
		'width'              => 1000,
		'height'             => 250,
		'flex-width'         => true,
		'flex-height'        => true,
	);
    add_theme_support('custom-header', $args);
}
add_action('after_setup_theme', 'webonary2_custom_header_setup');

// set preferred datatables version for bootstrap 5
add_filter('gdoc_enqueued_front_end_scripts', 'Webonary2_Customize::FilterDataTablesScriptsBootstrap5');
add_filter('gdoc_enqueued_front_end_styles', '\Webonary2_Customize::FilterDataTablesStylesBootstrap5');

// Customizer additions.
Webonary2_Customize::Init();
