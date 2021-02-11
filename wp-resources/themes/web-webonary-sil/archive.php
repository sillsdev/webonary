<?php
/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * Methods for TimberHelper can be found in the /functions sub-directory
 *
 * @package 	WordPress
 * @subpackage 	Timber
 * @since 		Timber 0.2
 */

use Timber\Timber;
 // not yet reviewed

$context = Timber::get_context();

$templates = array('archive.twig');

$context['wp_title'] = 'Archive';
if (is_day()){
	$context['wp_title'] = 'Archive&nbsp;&nbsp;>&nbsp;&nbsp;'.get_the_date( 'D M Y' );
} else if (is_month()){
	$context['wp_title'] = 'Archive&nbsp;&nbsp;>&nbsp;&nbsp;'.get_the_date( 'M Y' );
} else if (is_year()){
	$context['wp_title'] = 'Archive&nbsp;&nbsp;>&nbsp;&nbsp;'.get_the_date( 'Y' );
} else if (is_tag()){
	$context['wp_title'] = single_tag_title('', false);
} else if (is_category()){
	$context['wp_title'] = single_cat_title('', false);
	array_unshift($templates, 'archive-'.get_query_var('cat').'.twig');
} else if (is_post_type_archive()){
	$context['wp_title'] = post_type_archive_title('', false);
	array_unshift($templates, 'archive-'.get_post_type().'.twig');
}

$context['posts'] = Timber::get_posts();
$context['logo'] = Timber::get_widgets('logo');
$context['section_license'] = Timber::get_widgets('section_license');
$context['section_donate'] = Timber::get_widgets('section_donate');
$context['sidebar_main'] = Timber::get_widgets('sidebar_main');
$context['footer_sil'] = Timber::get_widgets('footer_sil');
$context['footer_software'] = Timber::get_widgets('footer_software');
$context['footer_fonts'] = Timber::get_widgets('footer_fonts');
$context['footer_contact'] = Timber::get_widgets('footer_contact');

Timber::render($templates, $context);
