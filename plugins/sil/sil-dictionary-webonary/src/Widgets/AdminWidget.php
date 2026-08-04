<?php

namespace SIL\Webonary\Widgets;

use Exception;
use SIL\Webonary\Abstracts\AdminReportTrait;
use SIL\Webonary\Admin;
use SIL\Webonary\AdminReportTable;
use SIL\Webonary\Helpers\Cache;
use SIL\Webonary\Helpers\Request;
use SIL\Webonary\Reports;

class AdminWidget
{
	/**
	 * @return string
	 * @throws Exception
	 */
	public static function ShowWidget(): string
	{
		self::DoAction();
		$return_val = self::DisplayOptions();
		return $return_val . Admin::DoAdminNotices();
	}

	/**
	 * @return void
	 * @throws Exception
	 * @codeCoverageIgnore
	 */
	private static function DoAction(): void
	{
		if (Request::PostStr('clear_all_cache') == 'clear all cache') {
			Cache::DeleteAllForAllDictionaries();
			Admin::AddAdminNotice('success', 'Local cache cleared for all dictionaries.');
			return;
		}

		if (Request::PostStr('clear_all_cloudflare') == 'clear all cloudflare') {
			Cache::DeleteCloudflareForAllDictionaries();
			Admin::AddAdminNotice('success', 'Cloudflare cache cleared for all dictionaries.');
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

		$lines[] = self::DisplayVersion();

		// closing tags
		$lines[] = '</form>';
		$lines[] = '</div>';

		$return_val = implode(PHP_EOL, $lines);

// @codeCoverageIgnoreStart
		if (!defined('PHP_UNIT'))
			echo $return_val;
// @codeCoverageIgnoreEnd

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

// @codeCoverageIgnoreStart
		if (!defined('PHP_UNIT'))
			echo $return_val;
// @codeCoverageIgnoreEnd

		return $return_val;
	}

	private static function DisplayCacheControl(): string
	{
		$rows[] = <<<'HTML'
<tr>
	<td>
		<button class="button button-webonary" type="submit" name="clear_all_cache" value="clear all cache">Clear All Local Cache</button>
		<p style="margin: 0.4rem 0 0"><span style="font-weight: 700">WARNING:</span> This will clear the local cache for ALL dictionaries. This may take a few minutes.</p>
	</td>
</tr>
HTML;

		$site = get_site();

		// only clear Cloudflare for webonary.org
// @codeCoverageIgnoreStart
		if (str_contains($site->domain, 'webonary.org')) {
			$rows[] = <<<'HTML'
<tr>
	<td>
		<button class="button button-webonary" type="submit" name="clear_all_cloudflare" value="clear all cloudflare" style="margin: 2rem 0 0">Clear All Cloudflare Cache</button>
		<p style="margin: 0.4rem 0 0"><span style="font-weight: 700">WARNING:</span> This will clear the Cloudflare cache for ALL dictionaries. This will result in a heavier load on the server until the cache has been rebuilt.</p>
	</td>
</tr>
HTML;
		}
// @codeCoverageIgnoreEnd

		$html = implode(PHP_EOL, $rows);

		return <<<HTML
<div class="webonary-admin-block">
	<div class="flex-column">
		<h4>Cache</h4>
		<table class="flex-table">
			<tbody>
			$html
			</tbody>
		</table>
	</div>
</div>
HTML;
	}

	private static function DisplayVersion(): string
	{
		$file_name = dirname(__DIR__) . '/version.txt';
		if (is_file($file_name))
			$version = trim(file_get_contents($file_name));

		if (empty($version))
			$version = 'Unknown version';

		return <<<HTML
<div class="webonary-admin-block">
	<div class="flex-column">
		<h4>Version</h4>
		<table class="flex-table">
			<tbody>
			<tr>
				<td>$version</td>
			</tr>
			</tbody>
		</table>
	</div>
</div>
HTML;
	}
}
