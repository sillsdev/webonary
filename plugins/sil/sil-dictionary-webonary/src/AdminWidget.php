<?php

namespace SIL\Webonary;

use SIL\Webonary\Abstracts\AdminReportTrait;
use SIL\Webonary\Helpers\Request;

class AdminWidget
{
	public static function ShowWidget(): string
	{
		$return_val = self::DisplayOptions();
		return $return_val . Admin::DoAdminNotices();
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
		];

		// closing tags
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
}
