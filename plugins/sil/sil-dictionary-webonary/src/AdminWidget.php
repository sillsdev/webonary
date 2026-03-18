<?php

namespace SIL\Webonary;

use SIL\Webonary\Abstracts\AdminReportTrait;
use SIL\Webonary\Helpers\Cache;
use SIL\Webonary\Helpers\Request;

class AdminWidget
{
	public static function ShowWidget(): string
	{
		self::DoAction();
		$return_val = self::DisplayOptions();
		return $return_val . Admin::DoAdminNotices();
	}

	private static function DoAction(): void
	{
		if (Request::PostStr('clear_all_cache') == 'clear all cache') {
			Cache::DeleteAllForAllDictionaries();
			Admin::AddAdminNotice('success', 'Cache cleared for all dictionaries.');
			return;
		}
	}

	public static function ShowReports(): string
	{
		$return_val = self::DisplayReports();
		return $return_val . Admin::DoAdminNotices();
	}

	public static function DisplayOptions(): string
	{
		// opening tags
		$lines = [
			'<div class="wrap">',
			'<h1>' . __('Webonary Admin Tools', 'sil_dictionary') . '</h1>',
			'<form id="configuration-form" method="post" action="">'
		];

		$lines[] = self::DisplayCacheControl();

		// closing tags
		$lines[] = '</form>';
		$lines[] = '</div>';

		$return_val = implode(PHP_EOL, $lines);

		if (!defined('PHP_UNIT'))
			echo $return_val;

		return $return_val;
	}

	public static function DisplayReports(): string
	{
		$report_id = Request::GetStr('report-id');

		if ($report_id == '')
			return self::DisplayListOfReports();

		$class = Reports::GetByID($report_id);
		if (empty($class)) {
			Admin::AddAdminNotice('warning', 'The requested report was not found.');
			return self::DisplayListOfReports();
		}

		$class = str_replace('::', '\\', $class);

		/** @var AdminReportTrait $report */
		$report = new $class();

		return $report->Run();
	}

	private static function DisplayListOfReports(): string
	{
		// opening tags
		$lines = [
			'<div class="wrap">',
			'<h1>' . __('Webonary Reports', 'sil_dictionary') . '</h1>',
		];

		$language_list = new AdminReportTable();
		$language_list->prepare_items();

		ob_start();
		$language_list->display();
		$lines[] = ob_get_clean();

		// closing tags
		$lines[] = '</div>';

		$return_val = implode(PHP_EOL, $lines);

		if (!defined('PHP_UNIT'))
			echo $return_val;

		return $return_val;
	}

	private static function DisplayCacheControl(): string
	{
		return <<<HTML
<div class="webonary-admin-block">
	<div class="flex-column">
		<h4>Cache</h4>
		<table class="flex-table">
			<tbody>
			<tr>
				<td>
					<button class="button button-webonary" type="submit" name="clear_all_cache" value="clear all cache">Clear All Cache</button>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
</div>
HTML;
	}
}
