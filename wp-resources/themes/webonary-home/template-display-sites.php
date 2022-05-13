<?php
/*
Template Name: Display Sites
*/

add_filter('body_class','full_width_body_classes');

$url = strtolower($_SERVER['REQUEST_URI']);
$is_excel = strpos($url, 'excel') !== false;

if ($is_excel) {
	/** @noinspection PhpUnhandledExceptionInspection */
	Webonary_Excel::DisplayAllSites(true);
	exit();
}

get_header();

/** @noinspection PhpUnhandledExceptionInspection */
Webonary_Excel::DisplayAllSites(false);

get_footer();
