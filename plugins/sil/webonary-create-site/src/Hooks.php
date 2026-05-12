<?php

namespace SIL\WebonaryCreateSite;

class Hooks
{
	public static function SetHooks(): int
	{
		if (wp_installing())
			return 0;

		return self::SetAdminHooks();
	}

	private static function SetAdminHooks(): int
	{
		if (!is_admin())
			return 0;

		$hooks_set = (int)add_action('add_link', [self::class, 'AddLink']);
		$hooks_set += add_action('wpcf7_init', [self::class, 'AddFormTagSubdirectory']);
		$hooks_set += add_filter('wpcf7_validate_subdirectory*', [self::class, 'SubdirectoryValidationFilter'], 20, 2);
		$hooks_set += add_filter('wp_mail_from', [self::class, 'WebonaryFromEmail']);
		$hooks_set += add_filter('wp_mail_from_name', [self::class, 'WebonaryEmailFromName']);
		$hooks_set += add_action('network_admin_menu',[self::class, 'MultiSiteAddPage']);
		$hooks_set += add_filter('manage_sites_action_links', [self::class, 'AddSiteAction'], 10, 2);

		return $hooks_set;
	}

	public static function AddLink($link_id): void
	{
		global $wpdb;

		// make sure the value is only digits (BIGINT UNSIGNED)
		if (!ctype_digit($link_id))
			return;

		$sql = <<<SQL
SELECT b.blog_id, l.link_url, l.link_name
FROM webonary.wp_links AS l
  INNER JOIN webonary.wp_blogs AS b ON b.path <> '/' AND l.link_url LIKE CONCAT('%', b.path)
WHERE link_id = $link_id
SQL;

		$blog = $wpdb->get_row($sql);

		$sql = "SELECT option_value FROM wp_{$blog->blog_id}_options WHERE option_name LIKE 'admin_email'";

		$admin_email = $wpdb->get_var($sql);

		$msg = <<<TXT
Congratulations! The $blog->link_name dictionary has been published on https://www.webonary.org/. In a few days it will appear in the Open Language Archives Community catalogue, https://www.language-archives.org/archive/webonary.org.

If you have any questions or concerns, please reply to this email.

Thank you for giving us the privilege of serving you.

The Webonary team

TXT;

		$headers[] = 'From: Webonary <accounts@webonary.org>';
		$headers[] = 'Bcc: Webonary <accounts@webonary.org>';
		wp_mail($admin_email, 'Webonary Dictionary got published', $msg, $headers);
	}

	public static function AddFormTagSubdirectory(): void
	{
		wpcf7_add_form_tag('subdirectory*', [self::class, 'SubdirectoryFormTagHandler'], ['name-attr' => true]);
	}

	public static function SubdirectoryFormTagHandler($tag): string
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

	public static function SubdirectoryValidationFilter($result, $tag)
	{
		if (!preg_match('/^[a-z0-9_-]+$/', $_POST[$tag->name]))
			$result->invalidate($tag, 'Please use only lowercase letters a through z, numbers, dashes, or underscores.');

		return $result;
	}

	public static function WebonaryFromEmail($original_email_address): string
	{
		if (str_starts_with(strtolower($original_email_address), 'wordpress@'))
			return 'accounts@webonary.org';

		return $original_email_address;
	}

	public static function WebonaryEmailFromName($original_email_from)
	{
		if (str_starts_with(strtolower($original_email_from), 'wordpress'))
			return 'Webonary';

		return $original_email_from;
	}

	public static function MultiSiteAddPage(): void
	{
		add_submenu_page(
			'sites.php',
			Copier::$name,
			Copier::$name,
			'manage_sites',
			Copier::$domain,
			[Copier::class, 'AdminPage']
		);
	}

	/**
	 * Add "Copy Blog" link under each site in the sites list view.
	 *
	 * @param array $actions
	 * @param int $blog_id
	 * @return array $actions
	 */
	public static function AddSiteAction(array $actions, int $blog_id): array
	{
		if (is_main_site($blog_id))
			return $actions;

		$url = add_query_arg(
			[
				'page' => Copier::$domain,
				'blog' => $blog_id
			],
			network_admin_url('sites.php')
		);
		$nonce_string = sprintf('%s-%s', Copier::$domain, $blog_id);
		$actions[Copier::$domain] = '<a href="' . esc_url(wp_nonce_url($url, $nonce_string)) . '">Copy</a>';

		return $actions;
	}
}
