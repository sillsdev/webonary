<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * To generate specific templates for your pages you can use:
 * /mytheme/views/page-mypage.twig
 * (which will still route through this PHP file)
 * OR
 * /mytheme/page-mypage.php
 * (in which case you'll want to duplicate this file and save to the above path)
 *
 * Methods for TimberHelper can be found in the /functions sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

use Timber\Timber;
use Timber\Page;

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
if (is_front_page() or is_page( 'HomeTemp' )) {
  $context['subtitle'] = Timber::get_widgets('subtitle');
  $context['sidebar_home'] = Timber::get_widgets('sidebar_home');
  $context['home_trio_1'] = Timber::get_widgets('home_trio_1');
	$context['home_trio_2'] = Timber::get_widgets('home_trio_2');
	$context['home_trio_3'] = Timber::get_widgets('home_trio_3');
	$context['home_testimonial'] = Timber::get_widgets('home_testimonial');
	$context['section_faq_leader'] = Timber::get_widgets('section_faq_leader');
	$context['section_blog_recent'] = Timber::get_widgets('section_blog_recent');
  if (is_main_site()) {
    $templates = array('home-root.twig');
  } else {
	  $templates = array('home.twig');
  }
} elseif (strstr($post->slug, 'browse') !== false) {
	$templates = array('page-browse.twig', 'page.twig');
} else {
  $context['sidebar_main'] = Timber::get_widgets('sidebar_main');
	$templates = array('page-' . $post->post_name . '.twig', 'page.twig');
}
Timber::render($templates, $context);
