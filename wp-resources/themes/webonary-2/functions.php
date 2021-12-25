<?php

const WEBONARY_THEME_DOMAIN = 'webonary2';
$webonary2_class_path = get_template_directory() . DS . 'classes';

if (!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

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
	add_action('wp_enqueue_scripts', array('Webonary2_Customize', 'UnqueueJquery'), 5);
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
	add_theme_support(
		'html5',
		array(
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		)
	);

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
}
add_action('after_setup_theme', 'webonary2_setup');

function webonary2_menu_item_class($classes, $item, $args)
{
	if ($args->container_id != 'webonary-main-menu')
		return $classes;

	if (!in_array('nav-item', $classes))
		$classes[] = 'nav-item';

	if (in_array('menu-item-has-children', $item->classes))
		$classes[] = 'dropdown';

	return $classes;
}
add_filter('nav_menu_css_class', 'webonary2_menu_item_class', 10, 3);

function webonary2_menu_link_attributes($atts, $item, $args)
{
	if ($item->current)
		$atts['class'] = 'nav-link active';
	else
		$atts['class'] = 'nav-link';

	if (in_array('menu-item-has-children', $item->classes))
		$atts['class'] .= ' dropdown-toggle';

	return $atts;
}
add_filter('nav_menu_link_attributes', 'webonary2_menu_link_attributes', 10, 4);

function webonary2_nav_menu_items($items, $args)
{
	$args = func_get_args();

	return $items;
}
add_filter('wp_nav_menu_items', 'webonary2_nav_menu_items', 10, 4);

// Customizer additions.
Webonary2_Customize::Init();
