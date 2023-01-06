<?php

// style and scripts
add_action('wp_enqueue_scripts', 'webonary_bootstrap_enqueue_styles');
function webonary_bootstrap_enqueue_styles(): void
{

  // style.css
  wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

  // Compiled main.css
  $modified_bootscoreChildCss = date('YmdHi', filemtime(get_stylesheet_directory() . '/css/main.css'));
  wp_enqueue_style('main', get_stylesheet_directory_uri() . '/css/main.css', array('parent-style'), $modified_bootscoreChildCss);

  // custom.js
  wp_enqueue_script('custom-js', get_stylesheet_directory_uri() . '/js/custom.js', false, '', true);
}

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function webonary_bootstrap_widgets_init(): void
{

	// qTranslate-XT language chooser
	register_sidebar(array(
		'name'          => esc_html__('Language Chooser', 'webonary_bootstrap'),
		'id'            => 'language-chooser',
		'description'   => esc_html__('Put qTranslate Language Chooser widget here.', 'webonary_bootstrap'),
		'before_widget' => '<div class="ms-3">',
		'after_widget'  => '</div>',
		'before_title'  => '<div class="widget-title d-none">',
		'after_title'   => '</div>'
	));

	register_sidebar(array(
		'name'          => esc_html__('Bottom of Content', 'webonary_bootstrap'),
		'id'            => 'bottom-content-sidebar',
		'description'   => esc_html__('This will display below the content, above the footer.', 'webonary_bootstrap'),
		'before_widget' => '<div class="mb-3">',
		'after_widget'  => '</div>',
		'before_title'  => '<div class="widget-title d-none">',
		'after_title'   => '</div>'
	));
}
add_action('widgets_init', 'webonary_bootstrap_widgets_init');

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function webonary_bootstrap_setup(): void
{
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on Bootscore, use a find and replace
	 * to change 'bootscore' to the name of your theme in all the template files.
	*/
	load_theme_textdomain('webonary_bootstrap', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'webonary_bootstrap_setup');



function webonary_bootstrap_options(WP_Customize_Manager $wp_customize): void
{
	// https://wpmudev.com/blog/wordpress-theme-customization-api/
	$wp_customize->add_section(
		'webonary_bootstrap_custom_options',
		[
			'title' => __('Custom Settings', 'webonary_bootstrap'),
			'priority' => 100,
			'capability' => 'edit_theme_options',
			'description' => __('Change theme options here.', 'webonary_bootstrap')
		]
	);

	$wp_customize->add_setting('header_bg_color', ['default' => '#f8f9fa', 'transport' => 'postMessage']);
	$wp_customize->add_setting('header_text_color', ['default' => '#000000', 'transport' => 'postMessage']);
	$wp_customize->add_setting('footer_bg_color', ['default' => '#85005B', 'transport' => 'postMessage']);
	$wp_customize->add_setting('footer_text_color', ['default' => '#ffffff', 'transport' => 'postMessage']);
	$wp_customize->add_setting('highlight_bg_color', ['default' => '#85005B', 'transport' => 'postMessage']);
	$wp_customize->add_setting('highlight_text_color', ['default' => '#ffffff', 'transport' => 'postMessage']);

	$wp_customize->add_control(new WP_Customize_Color_Control(
		$wp_customize,
		'header_bg_color_control',
		[
			'label' => __('Header Background Color', 'webonary_bootstrap'),
			'section' => 'webonary_bootstrap_custom_options',
			'settings' => 'header_bg_color',
			'priority' => 10
		]
	));

	$wp_customize->add_control(new WP_Customize_Color_Control(
		$wp_customize,
		'header_text_color_control',
		[
			'label' => __('Header Text Color', 'webonary_bootstrap'),
			'section' => 'webonary_bootstrap_custom_options',
			'settings' => 'header_text_color',
			'priority' => 20
		]
	));

	$wp_customize->add_control(new WP_Customize_Color_Control(
		$wp_customize,
		'footer_bg_color_control',
		[
			'label' => __('Footer Background Color', 'webonary_bootstrap'),
			'section' => 'webonary_bootstrap_custom_options',
			'settings' => 'footer_bg_color',
			'priority' => 30
		]
	));

	$wp_customize->add_control(new WP_Customize_Color_Control(
		$wp_customize,
		'footer_text_color_control',
		[
			'label' => __('Footer Text Color', 'webonary_bootstrap'),
			'section' => 'webonary_bootstrap_custom_options',
			'settings' => 'footer_text_color',
			'priority' => 40
		]
	));

	$wp_customize->add_control(new WP_Customize_Color_Control(
		$wp_customize,
		'highlight_color_control',
		[
			'label' => __('Highlight Background Color', 'webonary_bootstrap'),
			'section' => 'webonary_bootstrap_custom_options',
			'settings' => 'highlight_bg_color',
			'priority' => 50
		]
	));

	$wp_customize->add_control(new WP_Customize_Color_Control(
		$wp_customize,
		'highlight_text_color_control',
		[
			'label' => __('Highlight Text Color', 'webonary_bootstrap'),
			'section' => 'webonary_bootstrap_custom_options',
			'settings' => 'highlight_text_color',
			'priority' => 50
		]
	));
}
add_action('customize_register' , 'webonary_bootstrap_options');


function webonary_bootstrap_customizer_preview(): void
{
	wp_enqueue_script(
		'webonary_bootstrap_theme_customizer',
		get_stylesheet_directory_uri() . '/js/theme-customizer.js',
		array(  'jquery', 'customize-preview' ),
		'',
		true
	);
}
add_action( 'customize_preview_init' , 'webonary_bootstrap_customizer_preview' );
