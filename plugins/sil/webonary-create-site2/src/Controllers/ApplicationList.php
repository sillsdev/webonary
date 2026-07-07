<?php

namespace SIL\WebonaryCreateSite2\Controllers;

use SIL\WebonaryCreateSite2\Models\Applications;

class ApplicationList
{
	public static function DisplayApplicationList(): string
	{
		$applications = Applications::GetActiveApplications();

		$title = get_admin_page_title();

		if (empty($applications))
			$sub_title = __('All applications have been processed', 'webonary-create-site2');
		else
			$sub_title = __('New Applications', 'webonary-create-site2');

		$rows = [];
		$app_template = <<<'HTML'
<tr>
	<td>%1$s</td>
	<td>%2$s</td>
	<td>%3$s</td>
	<td>%4$s</td>
	<td>%5$s</td>
</tr>
HTML;

		$admin_url = admin_url('/network/sites.php?page=webonary-create-site-2');
		$delete = __('Delete', 'webonary-create-site2');

		foreach ($applications as $app) {

			$timestamp = date('D, j M Y H:i:s', floor($app->Timestamp));
			$desired_url = self::GetDesiredSiteURL($app->DesiredUrl);
			$language_url = $admin_url . '&app-id=' . $app->ID;
			$language_link = '<a href="' . $language_url . '">' . $app->LanguageName . '</a>';

			$remove_link = <<<HTML
<form method="post" action="$admin_url" onsubmit="return confirmDeleteApplication('$app->LanguageName');">
	<input type="hidden" name="app-id" value="$app->ID">
	<span class="delete">
	    <button type="submit" name="remove" value="$app->ID" class="button-link">$delete</button>
    </span>
</form>
HTML;

			$rows[] = sprintf(
				$app_template,
				$timestamp,
				$language_link,
				$desired_url,
				$app->FromEmail,
				$remove_link
			);
		}

		$rows_html = implode(PHP_EOL, $rows);
		$timestamp = __('Timestamp', 'webonary-create-site2');
		$language = __('Language', 'webonary-create-site2');
		$desired_url = __('Desired URL', 'webonary-create-site2');
		$contact_email = __('Contact Email', 'webonary-create-site2');

		return <<<HTML
<div class="wrap">
    <h1 class="wp-heading-inline">$title</h1>

	<div style="display: flex; align-items: center; justify-content: space-between">
		<h2>$sub_title</h2>
		<div><a href="$admin_url&app-id=new" class="button button-compact">New Site</a></div>
	</div>
	<table class="wp-list-table widefat striped">
	<thead>
		<tr>
			<th>$timestamp</th>
			<th>$language</th>
			<th>$desired_url</th>
			<th>$contact_email</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		$rows_html
	</tbody>
	</table>
</div>
HTML;
	}

	private static function GetDesiredSiteURL(string $desired_url): string
	{
		$desired_url = preg_replace('/(https?:\/\/(www\.)?)/', '', $desired_url);
		$desired_url = str_replace('webonary.org', '', $desired_url);
		return trim($desired_url, "./ \t\n\r\0\x0B");
	}
}
