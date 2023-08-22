<?php

/*
Plugin Name: Webonary Create Site
Plugin URI: http://www.webonary.org
Description: This plugin helps with automating things when creating a new webonary site
Author: SIL International
Author URI: http://www.sil.org/
Text Domain: webonary-create-site
Version: 0.2
Stable tag: 0.1
License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

if (!defined('ABSPATH'))
	die('-1');

include_once 'WebonaryBlogCopier.php';
global $BlogCopier;
$BlogCopier = new WebonaryBlogCopier();

/** @noinspection SqlResolve */
function add_link_action($linkid): void
{
	global $wpdb;

	$sql = <<<SQL
SELECT blog_id, link_url, link_name
FROM wp_links
  INNER JOIN wp_term_relationships ON  wp_term_relationships.object_id = wp_links.link_id
  INNER JOIN wp_blogs ON wp_blogs.domain = replace(replace(wp_links.link_url, 'https://',''),'/','')
WHERE link_id = %d AND wp_term_relationships.term_taxonomy_id = 8
SQL;

	$blog = $wpdb->get_results($wpdb->prepare($sql, $linkid), ARRAY_A);

	$sql = "SELECT option_value FROM wp_{$blog[0]['blog_id']}_options WHERE option_name LIKE 'admin_email'";

	$admin_email = $wpdb->get_var($sql);

	$msg = <<<TXT
Congratulations! The {$blog[0]['link_name']} dictionary has been published on https://www.webonary.org/. In a few days it will appear in the Open Language Archives Community catalogue, https://www.language-archives.org/archive/webonary.org.

If you have any questions or concerns, please reply to this email.

Thank you for giving us the privilege of serving you.

The Webonary team

TXT;

	$headers[] = 'From: Webonary <webonary@sil.org>';
	$headers[] = 'Bcc: Webonary <webonary@sil.org>';
	wp_mail($admin_email, 'Webonary Dictionary got published', $msg, $headers);
}

add_action('add_link', 'add_link_action');

//20200207 chungh: validate subdirectory name
add_action('wpcf7_init', 'custom_add_form_tag_subdirectory');

function custom_add_form_tag_subdirectory(): void
{
	wpcf7_add_form_tag('subdirectory*', 'custom_subdirectory_form_tag_handler', array('name-attr' => true));
}

function custom_subdirectory_form_tag_handler($tag): string
{
	if (empty($tag->name))
		return '';

	$validation_error = wpcf7_get_validation_error($tag->name);

	$class = wpcf7_form_controls_class($tag->type);

	if ($validation_error)
		$class .= ' wpcf7-not-valid';

	$attributes = [
		'class' => $tag->get_class_option($class),
		'id' => $tag->get_id_option(),
		'tabindex' => $tag->get_option('tabindex', 'signed_int', true),
		'min' => $tag->get_option('min', 'signed_int', true),
		'max' => $tag->get_option('max', 'signed_int', true),
		'step' => $tag->get_option('step', 'int', true),
		'aria-invalid' => $validation_error ? 'true' : 'false',
		'type' => 'text',
		'name' => $tag->name
	];

	if ($tag->is_required())
		$attributes['aria-required'] = 'true';

	$value = (string)reset($tag->values);

	if ($tag->has_option('placeholder') || $tag->has_option('watermark')) {
		$attributes['placeholder'] = $value;
		$value = '';
	}

	$value = $tag->get_default_option($value);

	$value = wpcf7_get_hangover($tag->name, $value);

	$attributes['value'] = $value;


	$attributes = wpcf7_format_atts($attributes);

	/** @noinspection HtmlUnknownAttribute */
	return sprintf(
		'<span class="wpcf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
		sanitize_html_class($tag->name), $attributes, $validation_error);
}

add_filter('wpcf7_validate_subdirectory*', 'custom_subdirectory_validation_filter', 20, 2);
function custom_subdirectory_validation_filter($result, $tag)
{

	if (!preg_match('/^[a-z0-9_-]+$/', $_POST[$tag->name])) {
		$result->invalidate($tag, "Please use only lowercase letters a through z, numbers, dashes, or underscores.");
	}

	return $result;
}

// NB: Removed 22 Aug 2023, Webonary Issue #584
//// overwrites the wp_new_user_notification in includes/pluggable, so that no email with password reset gets sent out
//if (!function_exists('wp_new_user_notification')) {
//	function wp_new_user_notification($user_id, $notify = '')
//	{
//	}
//}
