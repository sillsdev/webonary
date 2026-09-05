<?php
/** @noinspection DuplicatedCode */

namespace SIL\WebonaryCreateSite2\Controllers;

use SIL\WebonaryCreateSite2\Abstracts\AppFields;
use SIL\WebonaryCreateSite2\Models\Application;
use SIL\WebonaryCreateSite2\Models\Applications;
use WP_User;
use wpdb;

class NewSite
{
	private static string $text_domain = 'webonary-create-site2';

	public static function DisplayNewSite(string $app_id = null): string
	{
		if (!empty($app_id) && $app_id != 'new') {
			$app = Applications::GetByID($app_id);
			$sub_title = sprintf(__('Language: %s', self::$text_domain), $app->LanguageName);
		}
		else {
			$app = new Application();
			$sub_title = '';
		}

		$rows = self::AddFields($app);
		$rows_html = implode(PHP_EOL, $rows);
		$title = __('Create Webonary Site', self::$text_domain);
		$button_text = __('Create Now', self::$text_domain);

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
		$field_label = __($field['label'], self::$text_domain);

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

			$text = __($text, self::$text_domain);

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

		$info = __($info, self::$text_domain);

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
		list($user_id, $password) = self::GetSiteAdmin();

		$to_blog_id = self::CreateNewBlog($user_id, $domain, $title);
		$msg = self::CopyTemplateToBlog($from_blog_id, $to_blog_id, $user_id, $password);

		$app_id = $_POST['app-id'];
		if ($app_id != 'new')
			Applications::GetByID($app_id)->MarkCreated();

		$return_val = json_encode(['status' => 'OK', 'msg' => $msg]);

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
			$messages[] = __('Please select a Source Site to Copy.', self::$text_domain);

		$domain = sanitize_user(str_replace('/', '', $_POST['desired-url']));
		if (empty($domain))
			$messages[] = __('Please enter a New Site Address.', self::$text_domain);

		$title = $_POST['language-name'];
		if (empty($title))
			$messages[] = __('Please enter a New Site Title.', self::$text_domain);

		return [$messages, $from_blog_id, $domain, $title];
	}

	private static function GetSiteAdmin(): array
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
				$error = 'An existing user with the email "' . $user_email . '" was found, but the username is "' . $row->user_login . '"';

			if ($row->user_email != $user_email)
				$error = 'An existing user with the username "' . $user_name . '" was found, but the email is "' . $row->user_email . '"';

			// if no error, return the user ID
			if (empty($error))
				return [$row->ID, 'N/A'];

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

		return [$user_id, $password];
	}

	private static function CreateNewBlog(int $owner_user_id, string $domain, string $title): int
	{
		/** @var $wpdb wpdb */
		/** @var $base string Set in wp-config.php */
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
		$to_blog_id = wpmu_create_blog($new_domain, $path, $title, $owner_user_id, ['public' => 1], $current_site->id);
		$wpdb->show_errors();

		if (is_wp_error($to_blog_id)) {
			// notify the user that there is an error
			echo json_encode(['errors' => [$to_blog_id->get_error_message()]]);
			exit();
		}

		switch_to_blog($to_blog_id);

		$user = new WP_User($owner_user_id);
		$user->set_role('editor');

		restore_current_blog();

		return $to_blog_id;
	}

	private static function CopyTemplateToBlog($from_blog_id, $to_blog_id, $owner_user_id, $password): string
	{
		/** @var $wpdb wpdb */
		global $wpdb;

		$to_blog_info = get_blog_details(['blog_id' => $to_blog_id]);
		wpmu_welcome_notification($to_blog_id, $owner_user_id, $password, $to_blog_info->blogname, ['public' => 1]);

		$dashboard_blog = get_site();
		if (!is_super_admin() && get_user_option('primary_blog', $owner_user_id) == $dashboard_blog->blog_id)
			update_user_option($owner_user_id, 'primary_blog', $to_blog_id, true);

		if (!$from_blog_id)
			return '';

		self::CopyBlogData($from_blog_id, $to_blog_id);
		self::CopyBlogFiles($from_blog_id, $to_blog_id);

		// make sure the protocol is HTTPS if not localhost
		$site_url = $to_blog_info->siteurl;
		if (str_starts_with($site_url, 'http://') && !str_contains($site_url, 'localhost')) {
			$site_url = str_replace('http://', 'https://', $site_url);
			update_blog_option($to_blog_id, 'siteurl', $site_url);
		}

		$user_email = $_POST['from_email'] ?? 'webonary@sil.org';

		$body = <<<TXT
From: [your-name] <[your-email]>
Subject: [your-subject]

Message Body:
[your-message]

--
This e-mail was sent from a contact form on $site_url

TXT;
		$contact_options = [
			'active' => true,
			'subject' => '[text* your-subject]',
			'sender' => '[your-name] <wordpress@webonary.org>',
			'recipient' => $user_email,
			'body' => $body,
			'additional_headers' => 'Reply-To: [your-email]',
			'attachments' => '',
			'use_html' => false,
			'exclude_blank' => false
		];

		$prefix = $wpdb->get_blog_prefix($to_blog_id);
		$sql = $wpdb->prepare("UPDATE {$prefix}_postmeta SET meta_value = %s WHERE meta_key = '_mail'", maybe_serialize($contact_options));
		$wpdb->query($sql);

		//Set footer (copyright)
		$theme_options = get_blog_option($to_blog_id, 'themezee_options');
		$theme_options['themeZee_footer'] = $to_blog_info->blogname . ' © ' . date('Y') . ' [copyright]';
		update_blog_option($to_blog_id, 'themezee_options', $theme_options);

		//Set copyright holder
		update_blog_option($to_blog_id, 'copyrightHolder', $_POST['copyright-holder'] ?? 'Unknown');

		// set the ethnologue and bibliography menu links
		$ethnologue_code = $_POST['language-iso-code'] ?? 'unk';
		$links_set = self::UpdateLinks($to_blog_id, $ethnologue_code);

		//Set country name
		update_blog_option($to_blog_id, 'countryName', $_POST['country-name'] ?? '');

		//Set region name
		update_blog_option($to_blog_id, 'regionName', $_POST['region'] ?? '');

		//Set publication status
		update_blog_option($to_blog_id, 'publicationStatus', $_POST['the-publication-status-of-the-dictionary'] ?? '0');

		//Set allow comments
		$allow_comments = isset($_POST['allow-comments']);
		update_blog_option($to_blog_id, 'default_comment_status', $allow_comments ? 'open' : 'closed');

		// 30 Oct 2023: default all new sites to cloud backend
		update_blog_option($to_blog_id, 'useCloudBackend', 1);

		// sprintf(__('Copied: %s in %s seconds', self::$text_domain), '<a href="' . $site_url . '" target="_blank">' . $to_blog_info->blogname . '</a>', number_format_i18n(timer_stop())),
		$msg = [
			"Welcome email sent to: $user_email",
			'Set this email as contact person for contact form.',
			'Set the copyright text in footer',
			'Set the Publication status',
			'Allow comments was set to \'' . $allow_comments ? 'Yes' : 'No' . '\''
		];

		if (!empty($links_set)) {
			$links = [];
			foreach ($links_set as $link) {
				$links[] = "<li>$link</li>";
			}
			$link_str = implode('      ' . PHP_EOL, $links);
			$msg[] = <<<HTML
Set the following links:
    <ul>
      $link_str
    </ul>
HTML;
		}

		$msg = implode('</li>' . PHP_EOL . '  <li>', $msg);
		$line1 = sprintf(__('Copied: %s in %s seconds', self::$text_domain), '<a href="' . $site_url . '" target="_blank">' . $to_blog_info->blogname . '</a>', number_format_i18n(timer_stop()));

		$msg = <<<HTML
$line1
<ul>
  <li>$msg</li>
</ul>
HTML;

		do_action('log', __('Copy Complete!', self::$text_domain), self::$text_domain, $msg);
		do_action('copy_blog_complete', $from_blog_id, $to_blog_id);

		return $msg;
	}

	private static function CopyBlogData(int $from_blog_id, int $to_blog_id): void
	{
		/** @var $wpdb wpdb */
		global $wpdb;

		$from_blog_prefix = $wpdb->get_blog_prefix($from_blog_id);
		$to_blog_prefix = $wpdb->get_blog_prefix($to_blog_id);
		$from_blog_prefix_length = strlen($from_blog_prefix);
		$from_blog_escaped_prefix = str_replace('_', '\_', $from_blog_prefix);

		// Grab key options from new blog.
		$saved_options = [
			'siteurl' => '',
			'home' => '',
			'upload_path' => '',
			'fileupload_url' => '',
			'upload_url_path' => '',
			'admin_email' => '',
			'blogname' => ''
		];

		// Options that should be preserved in the new blog.
		$saved_options = apply_filters('copy_blog_data_saved_options', $saved_options);
		foreach ($saved_options as $option_name => $option_value) {
			$saved_options[$option_name] = get_blog_option($to_blog_id, $option_name);
		}

		// Copy over ALL the tables.
		$query = $wpdb->prepare('SHOW TABLES LIKE %s', $from_blog_escaped_prefix . '%');
		do_action('log', $query, self::$text_domain);
		$old_tables = $wpdb->get_col($query);

		foreach ($old_tables as $table) {
			$raw_table_name = substr($table, $from_blog_prefix_length);
			$new_table = $to_blog_prefix . $raw_table_name;

			$query = "DROP TABLE IF EXISTS $new_table";
			do_action('log', $query, self::$text_domain);
			$wpdb->get_results($query);

			$query = "CREATE TABLE IF NOT EXISTS $new_table LIKE $table";
			do_action('log', $query, self::$text_domain);
			$wpdb->get_results($query);

			$query = "INSERT INTO $new_table SELECT * FROM $table";
			do_action('log', $query, self::$text_domain);
			$wpdb->get_results($query);
		}

		switch_to_blog($to_blog_id);

		// caches will be incorrect after direct DB copies
		wp_cache_delete('notoptions', 'options');
		wp_cache_delete('alloptions', 'options');

		// apply key options from new blog.
		$prefix = $wpdb->get_blog_prefix($to_blog_id);
		foreach ($saved_options as $option_name => $option_value) {

			//if using update_option function for admin_email it will send an email to the original site owner
			if ($option_name == 'admin_email') {
				$sql = "UPDATE {$prefix}_options SET option_value = %s WHERE option_name = 'admin_email'";
				$wpdb->query($wpdb->prepare($sql, $option_value));
			}
			else {
				update_option($option_name, $option_value);
			}
		}

		// always default to cloud backend for new sites
		update_option('useCloudBackend', '1');

		// fix all options with the wrong prefix...
		$query = $wpdb->prepare("SELECT * FROM $wpdb->options WHERE option_name LIKE %s", $from_blog_escaped_prefix . '%');
		$options = $wpdb->get_results($query);
		do_action('log', $query, self::$text_domain, count($options) . ' results found.');
		if ($options) {
			foreach ($options as $option) {
				$raw_option_name = substr($option->option_name, $from_blog_prefix_length);
				$wpdb->update($wpdb->options, ['option_name' => $to_blog_prefix . $raw_option_name], ['option_id' => $option->option_id]);
			}

			// caches will be incorrect after direct DB copies
			wp_cache_delete('notoptions', 'options');
			wp_cache_delete('alloptions', 'options');
		}

		// Fix GUIDs on copied posts
		$from_blog_url = get_blog_option($from_blog_id, 'siteurl');
		$to_blog_url = get_blog_option($to_blog_id, 'siteurl');
		$query = $wpdb->prepare("UPDATE {$to_blog_prefix}posts SET guid = REPLACE(guid, %s, %s) WHERE ID > 0", $from_blog_url, $to_blog_url);
		do_action('log', $query, self::$text_domain);
		$wpdb->query($query);

		restore_current_blog();
	}

	private static function CopyBlogFiles(int $from_blog_id, int $to_blog_id): void
	{
		/** @var $wpdb wpdb */
		global $wpdb;

		set_time_limit(2400); // 60 seconds x 40 minutes
		@ini_set('memory_limit', '2048M');

		// Path to source blog files.
		switch_to_blog($from_blog_id);
		$dir_info = wp_upload_dir();
		$from = str_replace(' ', "\\ ", trailingslashit($dir_info['basedir']) . '*'); // * necessary with GNU cp, doesn't hurt anything with BSD cp
		restore_current_blog();
		$from = apply_filters('copy_blog_files_from', $from, $from_blog_id);

		// Path to destination blog files.
		switch_to_blog($to_blog_id);
		$dir_info = wp_upload_dir();
		$to = str_replace(' ', "\\ ", trailingslashit($dir_info['basedir']));
		restore_current_blog();
		$to = apply_filters('copy_blog_files_to', $to, $to_blog_id);

		// Shell command used to copy files.
		$command = apply_filters('copy_blog_files_command', sprintf("cp -Rfp %s %s", $from, $to), $from, $to);
		exec($command);

		// Replace URLs in post content
		$to_blog_prefix = $wpdb->get_blog_prefix($to_blog_id);
		$from_blog_url = get_blog_option($from_blog_id, 'siteurl');
		$to_blog_url = get_blog_option($to_blog_id, 'siteurl');
		$query = $wpdb->prepare("UPDATE {$to_blog_prefix}posts SET post_content = REPLACE(post_content, %s, %s) WHERE ID > 0", $from_blog_url, $to_blog_url);
		do_action('log', $query, self::$text_domain);
		$wpdb->query($query);
	}

	private static function UpdateLinks(int $blog_id, string $ethnologue_code): array
	{
		/** @var $wpdb wpdb */
		global $wpdb;
		$prefix = $wpdb->get_blog_prefix($blog_id);
		$return_val = [];

		$links = [
			'www.ethnologue.com/language' => 'www.ethnologue.com/language',
			'www.sil.org/resources/search/language' => 'www.sil.org/resources/search/language',
			'www.sil.org/search/node' => 'www.sil.org/resources/search/language'
		];

		$sql = <<<SQL
UPDATE {$prefix}_postmeta
SET meta_value = %s
WHERE meta_key = '_menu_item_url'
  AND meta_value LIKE %s
SQL;

		foreach($links as $key => $value) {
			$link = "https://$value/$ethnologue_code";
			$updated = $wpdb->query($wpdb->prepare($sql, $link, $key));
			if ($updated > 0)
				$return_val[] = $link;
		}

		return $return_val;
	}
}
