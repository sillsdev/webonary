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

// we will handle loading jQuery ourselves
function webonary2_enqueue_jquery()
{
	wp_deregister_script( 'jquery-core' );
	wp_deregister_script( 'jquery-migrate' );
}
add_action('wp_enqueue_scripts', 'webonary2_enqueue_jquery', 5);
add_action('login_enqueue_scripts', 'webonary2_enqueue_jquery', 5);

//function webonary2_enqueue_bootstrap()
//{
//	wp_register_style('webonary2_bootstrap_style', plugin_dir_url(__FILE__) . 'css/admin_styles.css', [], false, 'all');
//	wp_enqueue_style('webonary2_bootstrap_style');
//
//	wp_register_script('webonary2_bootstrap_script', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css', ['jquery-core'], '5.1.3', true);
//	wp_enqueue_script('webonary2_bootstrap_script');
//}


register_nav_menus(
	array(
		'primary' => esc_html__('Top Menu', WEBONARY_THEME_DOMAIN),
		'footer'  => esc_html__('Footer Menu', WEBONARY_THEME_DOMAIN),
	)
);

// Customizer additions.
Webonary2_Customize::init();
