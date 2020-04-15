<?php
/**
 * The main template file
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file
 *
 * Methods for TimberHelper can be found in the /functions sub-directory
 *
 * @package 	WordPress
 * @subpackage 	Timber
 * @since 		Timber 0.1
 */

if (!class_exists('Timber')){
	echo 'Timber not activated. Make sure you activate the plugin in <a href="/wp-admin/plugins.php#timber">/wp-admin/plugins.php</a>';
}
$context = Timber::get_context();
$context['logo'] = Timber::get_widgets('logo');
$context['posts'] = Timber::get_posts();
$context['section_license'] = Timber::get_widgets('section_license');
$context['section_donate'] = Timber::get_widgets('section_donate');
$context['sidebar_main'] = Timber::get_widgets('sidebar_main');
$context['footer_sil'] = Timber::get_widgets('footer_sil');
$context['footer_software'] = Timber::get_widgets('footer_software');
$context['footer_fonts'] = Timber::get_widgets('footer_fonts');
$context['footer_contact'] = Timber::get_widgets('footer_contact');
$templates = array('index.twig');

Timber::render($templates, $context);
