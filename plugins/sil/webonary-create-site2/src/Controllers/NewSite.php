<?php

namespace SIL\WebonaryCreateSite2\Controllers;

use SIL\WebonaryCreateSite2\Abstracts\AppFields;
use SIL\WebonaryCreateSite2\Models\Application;
use SIL\WebonaryCreateSite2\Models\Applications;

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
}
