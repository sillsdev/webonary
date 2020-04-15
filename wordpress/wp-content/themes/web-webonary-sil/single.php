<?php
/**
 * The Template for displaying all single posts
 *
 * Methods for TimberHelper can be found in the /functions sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

use Timber\Timber;

function make_crumbs($post) {
  $crumbs = array();
  $p = $post;
  // array_unshift($crumbs, array('name' => $p->post_name, 'title' => $p->post_title, 'url' => ''));
  while($p->post_parent != 0) {
    $p = new \Timber\Post($p->post_parent);
    array_unshift($crumbs, array('name' => $p->post_name, 'title' => $p->post_title, 'url' => $p->guid));
  }
  return $crumbs;
}

$context = Timber::get_context();
$post = new \Timber\Post();
$context['crumbs'] = make_crumbs($post);
$context['post'] = $post;
$context['logo'] = Timber::get_widgets('logo');
$context['section_license'] = Timber::get_widgets('section_license');
$context['section_donate'] = Timber::get_widgets('section_donate');
$context['footer_sil'] = Timber::get_widgets('footer_sil');
$context['footer_software'] = Timber::get_widgets('footer_software');
$context['footer_fonts'] = Timber::get_widgets('footer_fonts');
$context['footer_contact'] = Timber::get_widgets('footer_contact');
$context['sidebar_main'] = Timber::get_widgets('sidebar_main');

// $context['wp_title'] .= ' - ' . $post->title();
// $context['comment_form'] = TimberHelper::get_comment_form();

$templates = array('single-' . $post->ID . '.twig', 'single-' . $post->post_type . '.twig', 'single.twig');

Timber::render($templates, $context);
