<?php

namespace SIL\WebonaryCreateSite2\Controllers;

use SIL\WebonaryCreateSite2\Abstracts\AppFields;
use SIL\WebonaryCreateSite2\Models\Application;
use SIL\WebonaryCreateSite2\Models\Applications;
use wpdb;

class NewSite
{
	public static function DisplayNewSite(string $app_id = null): string
	{
		if (!empty($app_id) && $app_id != 'new') {
			$app = Applications::GetByID($app_id);
			$sub_title = sprintf(__('Language: %s', 'webonary-create-site2'), $app->LanguageName);
		}
		else {
			$app = new Application();
			$sub_title = '';
		}

		$rows = self::AddFields($app);
		$rows_html = implode(PHP_EOL, $rows);
		$title = __('Create Webonary Site', 'webonary-create-site2');
		$button_text = __('Create Now', 'webonary-create-site2');

		return <<<HTML
<div class="wrap">
    <h1 class="wp-heading-inline">$title</h1>
	<h2>$sub_title</h2>
	<form id="wcs2-configuration-form" method="post" action="" onsubmit="return false;">
		<input type="hidden" name="action" value="createNewSite">
		<input type="hidden" name="app-id" value="$app_id">
		<table class="form-table" role="presentation">
		<tbody>
			$rows_html
		</tbody>
		</table>
		<div class="wcs2-admin-block">
			<div style="margin: 1rem 0">
				<button type="button" name="create-now" class="button-primary" value="$app->ID" onclick="postNewSite();">$button_text</button>
			</div>
		</div>
	</form>
</div>
HTML;
	}

	private static function AddFields(Application $app): array
	{
		$rows = [];

		foreach (AppFields::$Fields as $field_name => $field) {

			if (!array_key_exists('tag', $field))
				continue;

			$rows[] = match ($field['tag']) {
				'select' => self::AddSelectField($field_name, $field, $app->GetFieldValue($field_name)),
				'label' => self::AddLabelField($field_name, $field, $app->GetFieldValue($field_name)),
				default => self::AddInputField($field_name, $field, $app->GetFieldValue($field_name))
			};
		}

		return $rows;
	}

	private static function AddSelectField($field_name, $field, $field_value): string
	{
		if (is_array($field['src']))
			$options = self::BuildOptionList($field['src'], $field_value);
		elseif ($field['src'] == 'site_list')
			$options = self::BuildSiteList($field_value);
		else
			$options = '';

		$info = self::BuildInfoBlock($field['info'] ?? '');
		$class = array_key_exists('class', $field) ? "class=\"{$field['class']}\"" : '';

		return <<<HTML
<tr>
	<th scope="row">
		<label for="$field_name">{$field['label']}</label>
	</th>
	<td>
		<select id="$field_name" name="$field_name" $class>
        	$options
        </select>
        $info
	</td>
</tr>
HTML;
	}

	private static function AddLabelField($field_name, $field, $field_value): string
	{
		$info = self::BuildInfoBlock($field['info'] ?? '');
		$field_label = __($field['label'], 'webonary-create-site2');

		return <<<HTML
<tr>
	<th scope="row">
		<label for="$field_name">$field_label</label>
	</th>
	<td>
		<div class="label-field" id="$field_name">$field_value</div>
		$info
	</td>
</tr>
HTML;
	}

	private static function AddInputField($field_name, $field, $field_value): string
	{
		if ($field['type'] == 'checkbox')
			return self::BuildCheckBox($field_name, $field, $field_value);

		return self::BuildTextBox($field_name, $field, $field_value);
	}

	private static function BuildOptionList(array $src, $field_value): string
	{
		$options = [];

		foreach ($src as $key => $text) {
			$selected = ($text == $field_value) ? 'selected' : '';

			$text = __($text, 'webonary-create-site2');

			$options[] = <<<HTML
<option value="$key" $selected>$text</option>
HTML;
		}

		return implode(PHP_EOL, $options);
	}

	private static function BuildSiteList($field_value): string
	{
		global $wpdb, $current_site;

		/** @noinspection SqlResolve */
		$query = <<<SQL
SELECT b.blog_id, TRIM(BOTH '/' FROM b.path) AS path FROM $wpdb->blogs AS b
WHERE b.site_id = $current_site->id && b.blog_id > 1 ORDER BY path LIMIT 10000
SQL;
		$blogs = $wpdb->get_results($query);
		$selected_blog = array_filter($blogs, fn($blog) => str_ends_with($field_value, '/' . $blog->path));
		if (!empty($selected_blog))
			$selected_blog_id = array_values($selected_blog)[0]->blog_id;
		else
			$selected_blog_id = 0;

		$options = ['<option></option>'];

		foreach ($blogs as $blog) {

			$selected = ($blog->blog_id == $selected_blog_id) ? 'selected' : '';

			$options[] = <<<HTML
<option value="$blog->blog_id" $selected>$blog->path</option>
HTML;
		}

		return implode(PHP_EOL, $options);
	}

	private static function BuildInfoBlock($info): string
	{
		if ($info == '')
			return '';

		$info = __($info, 'webonary-create-site2');

		return <<<HTML
<p class="field-info">$info</p>
HTML;
	}

	private static function BuildCheckBox($field_name, $field, $field_value): string
	{
		$info = self::BuildInfoBlock($field['info'] ?? '');
		$class = array_key_exists('class', $field) ? "class=\"{$field['class']}\"" : '';
		if (array_key_exists('checked', $field))
			$checked = $field['checked'] ? 'checked="checked"' : '';
		else
			$checked = ($field_value == '1') ? 'checked="checked"' : '';

		return <<<HTML
<tr>
	<th scope="row">
		<label for="$field_name">{$field['label']}</label>
	</th>
	<td>
		<input type="checkbox" id="$field_name" name="$field_name" $checked value="1" $class>
        $info
	</td>
</tr>
HTML;
	}

	private static function BuildTextBox($field_name, $field, $field_value): string
	{
		global $current_site;

		$info = self::BuildInfoBlock($field['info'] ?? '');
		$class = array_key_exists('class', $field) ? "class=\"{$field['class']}\"" : '';

		if ($field_name == 'desired-url')
			$prefix = '<span>' . $current_site->domain . '/</span>';
		else
			$prefix = '';

		return <<<HTML
<tr>
	<th scope="row">
		<label for="$field_name">{$field['label']}</label>
	</th>
	<td>
		<div class="$field_name">
			$prefix<input type="text" id="$field_name" name="$field_name" value="$field_value" $class>
		</div>
        $info
	</td>
</tr>
HTML;
	}

	public static function AjaxCreateSite(): string
	{
		if (!headers_sent())
			header('Content-Type: application/json');

		list($messages, $from_blog_id, $domain, $title) = self::CheckPostData();

		if (!empty($messages)) {
			echo json_encode(['errors' => $messages]);
			exit();
		}

		// get the admin user
		$user_id = self::GetSiteAdmin();

		self::CreateBlog($user_id, $from_blog_id, $domain, $title);


		$app_id = $_POST['app-id'];
		if ($app_id != 'new')
			Applications::GetByID($app_id)->MarkCreated();

		$return_val = json_encode(['status' => 'OK']);

		if (defined('PHP_UNIT'))
			return $return_val;

		echo $return_val;
		exit();
	}

	private static function CheckPostData(): array
	{
		$messages = [];

		$from_blog_id = (int)($_POST['template-to-use'] ?? 0);
		if (!$from_blog_id)
			$messages[] = __('Please select a Source Site to Copy.', 'webonary-create-site2');

		$domain = sanitize_user(str_replace('/', '', $_POST['desired-url']));
		if (empty($domain))
			$messages[] = __('Please enter a New Site Address.', 'webonary-create-site2');

		$title = $_POST['language-name'];
		if (empty($title))
			$messages[] = __('Please enter a New Site Title.', 'webonary-create-site2');

		return [$messages, $from_blog_id, $domain, $title];
	}

	private static function GetSiteAdmin(): int
	{
		/** @var $wpdb wpdb */
		global $wpdb;

		$user_name = $_POST['username'] ?? '';
		$user_email = $_POST['from_email'] ?? '';
		$error = null;

		if (!empty($user_name)) {

			// check for existing user with this username
			$sql = "SELECT ID, user_email, user_login FROM $wpdb->users WHERE user_login = %s";
			$row = $wpdb->get_row($wpdb->prepare($sql, $user_name));
		}

		if (empty($row->ID) && !empty($user_email)) {

			// check for existing user with this email
			$sql = "SELECT ID, user_email, user_login FROM $wpdb->users WHERE user_email = %s";
			$row = $wpdb->get_row($wpdb->prepare($sql, $user_email));
		}

		if (!empty($row->ID)) {

			// check the existing username and email
			if ($row->user_login != $user_name)
				$error = 'An existing user with the email "' . $user_email . '" was found, but the username is "' . $user_name . '"';

			if ($row->user_email != $user_email)
				$error = 'An existing user with the username "' . $user_name . '" was found, but the email is "' . $user_email . '"';

			// if no error, return the user ID
			if (empty($error))
				return $row->ID;

			// notify the user that there is an error
			echo json_encode(['errors' => [$error]]);
			exit();
		}

		// user doesn't exist already, create it now
		do_action('pre_network_site_new_created_user', $user_email);
		$password = wp_generate_password(12, false);
		$user_id = wpmu_create_user($user_name, $password, $user_email);

		wp_update_user([
			'ID' => $user_id, // this is the ID of the user you want to update.
			'first_name' => $_POST['first_name'] ?? '',
			'last_name' => $_POST['last_name'] ?? ''
		]);

		do_action('network_site_new_created_user', $user_id);

		return $user_id;
	}

	private static function CreateBlog(int $user_id, int $from_blog_id, string $domain, string $title): void
	{
		/** @var $wpdb wpdb */
		global $wpdb, $base, $current_site;

		if (is_subdomain_install()) {
			$new_domain = $domain . '.webonary.org';
			$path = $base;
		}
		else {
			$new_domain = $_SERVER['HTTP_HOST'];
			$path = '/' . $domain . '/';
		}

		// The new domain that will be created for the destination blog.
		$new_domain = apply_filters('copy_blog_domain', $new_domain, $domain);

		// The new path that will be created for the destination blog.
		$path = apply_filters('copy_blog_path', $path, $domain);

		$wpdb->hide_errors();
		$to_blog_id = wpmu_create_blog($new_domain, $path, $title, $user_id, ['public' => 1], $current_site->id);
		$wpdb->show_errors();
	}
}
