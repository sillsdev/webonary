<?php

namespace SIL\Webonary;

use Kcs\ClassFinder\Finder\Psr4Finder;
use SIL\Webonary\Attributes\Report;

class Reports
{
	private static ?array $reports = null;

	public static function ReportRoutes(): array
	{
		$return_val = array_filter(self::GetReports(), function ($a) { return $a['show_list']; });

		usort($return_val, function ($a, $b) {
			return $a['title'] <=> $b['title'];
		});

		return $return_val;
	}

	public static function GetByID(string $report_id): ?string
	{
		$reports = self::GetReports();
		if (array_key_exists($report_id, $reports))
			return $reports[$report_id]['class'];

		return null;
	}

	private static function GetReports(): array
	{
		if (isset(self::$reports))
			return self::$reports;

		$url = admin_url('admin.php?page=webonary-reports') . '&report-id=';
		self::$reports = [];

		$finder = new Psr4Finder('SIL\Webonary\Reports', __DIR__ . '/Reports');
		$finder->withAttribute(Report::class);

		$classes = array_keys(iterator_to_array($finder));

		foreach ($classes as $class) {

			$slug = [$class, 'GetReportSlug']();

			self::$reports[$slug] = [
				'class' => $class,
				'title' => [$class, 'GetReportTitle'](),
				'href' => $url . $slug,
				'show_list' => [$class, 'GetShowInList']()
			];
		}

		return self::$reports;
	}
}
