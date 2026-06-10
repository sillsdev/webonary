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
	<td></td>
</tr>
HTML;

		foreach ($applications as $app) {

			$timestamp = date('D, j M Y H:i:s', floor($app->Timestamp));
			$desired_url = self::GetDesiredSiteURL($app->DesiredUrl);
			$language_url = admin_url('/network/sites.php?page=webonary-create-site-2') . '&app_id=' . $app->ID;
			$language_link = '<a href="' . $language_url . '">' . $app->LanguageName . '</a>';
			$rows[] = sprintf(
				$app_template,
				$timestamp,
				$language_link,
				$desired_url,
				$app->FromEmail
			);
		}

		$rows_html = implode(PHP_EOL, $rows);

		return <<<HTML
<div class="wrap">
    <h1 class="wp-heading-inline">$title</h1>
	<h2>$sub_title</h2>
	<table class="wp-list-table widefat striped">
	<thead>
		<tr>
			<th>Timestamp</th>
			<th>Language</th>
			<th>Desired URL</th>
			<th>Contact Email</th>
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
