<?php
/**
 * The template for displaying Author Archive pages
 *
 * Methods for TimberHelper can be found in the /functions sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

global $wp_query;

$context = Timber::get_context();

$author = new TimberUser($wp_query->query_vars['author']);
$context['author'] = $author;
$context['wp_title'] = 'Author Archives: ' . $author->name();
$context['author_name'] = $author->name();
$context['logo'] = Timber::get_widgets('logo');
$context['posts'] = Timber::get_posts();
$context['section_license'] = Timber::get_widgets('section_license');
$context['section_donate'] = Timber::get_widgets('section_donate');
$context['sidebar_main'] = Timber::get_widgets('sidebar_main');
$context['footer_sil'] = Timber::get_widgets('footer_sil');
$context['footer_software'] = Timber::get_widgets('footer_software');
$context['footer_fonts'] = Timber::get_widgets('footer_fonts');
$context['footer_contact'] = Timber::get_widgets('footer_contact');
$templates = array('author.twig');

Timber::render($templates, $context);
