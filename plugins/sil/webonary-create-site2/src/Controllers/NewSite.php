<?php

namespace SIL\WebonaryCreateSite2\Controllers;

use SIL\WebonaryCreateSite2\Models\Application;
use SIL\WebonaryCreateSite2\Models\Applications;

class NewSite
{
	public static function DisplayNewSite(string $app_id = null): string
	{
		if (!empty($app_id)) {
			$app = Applications::GetByID($app_id);
			$sub_title = 'Language: ' . $app->LanguageName;
		}
		else {
			$app = new Application();
			$sub_title = '';
		}

		return <<<HTML
<div class="wrap">
    <h1 class="wp-heading-inline">Create Webonary Site</h1>
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
}
