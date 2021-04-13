<?php
use Timber\TimberMenu;

require_once('vendor/autoload.php');

define('THEME_URL', get_template_directory_uri());

add_theme_support('post-formats');
add_theme_support('post-thumbnails');
add_theme_support('menus');
$customerHeaderDefault = array(
		'default-image'          => '',
		'random-default'         => false,
		'width'                  => 0,
		'height'                 => 0,
		'flex-height'            => false,
		'flex-width'             => false,
		'default-text-color'     => '',
		'header-text'            => true,
		'uploads'                => true,
		'wp-head-callback'       => '',
		'admin-head-callback'    => '',
		'admin-preview-callback' => '',
);
add_theme_support('custom-header', $customerHeaderDefault);
add_theme_support('html5', array('comment-list', 'comment-form', 'search-form', 'gallery', 'caption'));
add_theme_support('woocommerce');
add_theme_support( 'site-logo' );

add_filter('get_twig', 'add_to_twig');
add_filter('timber_context', 'add_to_context');

add_action('wp_enqueue_scripts', 'ttp_load_scripts');
add_action('widgets_init', 'ttp_register_sidebars');

function add_to_context($data){
	/* this is where you can add your own data to Timber's context object */
	$data['header'] = get_custom_header();
	$data['menu'] = new \Timber\Menu('psnav');
	$data['home_url'] = home_url();
	$data['theme_url'] = get_template_directory_uri();

	// TODO remove
	$data['qux'] = '';

	return $data;
}

function add_to_twig($twig){
	/* this is where you can add your own fuctions to twig */
	$twig->addExtension(new Twig_Extension_StringLoader());
	$twig->addFilter('myfoo', new Twig_Filter_Function('myfoo'));
	$twig->addFilter('highlight', new Twig_Filter_Function('highlight', array('words')));
	return $twig;
}

function myfoo($text){
	$text .= ' bar!';
	return $text;
}

function highlight($text, $words){
	if (!empty($words)){
		$reg_ex = implode('|', explode(' ', $words));
		if (preg_match('/^.*(?=<a.*$)/', $text, $matches)){
			$tmp_text = $matches[0];
			$tmp_text = preg_replace('/(' . $reg_ex . ')/iu', '<span class="search-highlight">\0</span>', $tmp_text);
			preg_match('/<a.*$/', $text, $matches);
			$tmp_text .= $matches[0];		
		}
		else {
			$tmp_text = preg_replace('/(' . $reg_ex . ')/iu', '<span class="search-highlight">\0</span>', $text);
		}
		return $tmp_text;
	} else {
		return $text;
	}
}

define('VERSION', '0.0.1'); // TODO Move this to version.php CP 2017-02
function ttp_load_scripts(){

	wp_enqueue_script('angular',          THEME_URL . "/vendor_bower/angular/angular.js", array(), VERSION, true);
	wp_enqueue_script('angular-route',    THEME_URL . "/vendor_bower/angular-route/angular-route.js", array(), VERSION, true);
	wp_enqueue_script('angular-resource', THEME_URL . "/vendor_bower/angular-resource/angular-resource.js", array(), VERSION, true);
	wp_enqueue_script('angular-ui',       THEME_URL . "/vendor_bower/angular-ui-bootstrap-bower/ui-bootstrap-tpls.js", array(), VERSION, true);

	wp_enqueue_script('ng-app', THEME_URL . "/client/default/ng-app.js", array(), VERSION, true);

}

function ttp_register_sidebars() {
	register_sidebar(array(
		'name' => 'Sidebar - Main',
		'id' => 'sidebar_main',
		'before_widget' => '<div>',
		'after_widget' => '</div>',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	));
	register_sidebar(array(
		'name' => 'Sidebar - Home',
		'id' => 'sidebar_home',
		'before_widget' => '<div>',
		'after_widget' => '</div>',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	));
	register_sidebar(array(
		'name' => 'Logo',
		'id' => 'logo',
		'before_widget' => '',
		'after_widget' => '',
		'before_title' => '',
		'after_title' => '',
	));
	register_sidebar(array(
		'name' => 'Subtitle',
		'id' => 'subtitle',
		'before_widget' => '',
		'after_widget' => '',
		'before_title' => '',
		'after_title' => '',
	));
	register_sidebar(array(
		'name' => 'License',
		'id' => 'section_license',
		'before_widget' => '<div>',
		'after_widget' => '</div>',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	));
	register_sidebar(array(
		'name' => 'Donate',
		'id' => 'section_donate',
		'before_widget' => '<div>',
		'after_widget' => '</div>',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	));
	register_sidebar(array(
		'name' => 'Home Trio 1',
		'id' => 'home_trio_1',
		'before_widget' => '<div>',
		'after_widget' => '</div>',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	));
	register_sidebar(array(
		'name' => 'Home Trio 2',
		'id' => 'home_trio_2',
		'before_widget' => '<div>',
		'after_widget' => '</div>',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	));
	register_sidebar(array(
		'name' => 'Home Trio 3',
		'id' => 'home_trio_3',
		'before_widget' => '<div>',
		'after_widget' => '</div>',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	));
	register_sidebar(array(
		'name' => 'Home Testimonial',
		'id' => 'home_testimonial',
		'before_widget' => '<div>',
		'after_widget' => '</div>',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	));
	register_sidebar(array(
		'name' => 'FAQ Leader',
		'id' => 'section_faq_leader',
		'before_widget' => '<div>',
		'after_widget' => '</div>',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	));
	register_sidebar(array(
		'name' => 'Recent Blog Posts',
		'id' => 'section_blog_recent',
		'before_widget' => '<div>',
		'after_widget' => '</div>',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	));
	register_sidebar(array(
		'name' => 'Footer - SIL',
		'id' => 'footer_sil',
		'before_widget' => '<div>',
		'after_widget' => '</div>',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	));
	register_sidebar(array(
		'name' => 'Footer - Software',
		'id' => 'footer_software',
		'before_widget' => '<div>',
		'after_widget' => '</div>',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	));
	register_sidebar(array(
		'name' => 'Footer - Fonts',
		'id' => 'footer_fonts',
		'before_widget' => '<div>',
		'after_widget' => '</div>',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	));
	register_sidebar(array(
		'name' => 'Footer - Contact',
		'id' => 'footer_contact',
		'before_widget' => '<div>',
		'after_widget' => '</div>',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	));
}
