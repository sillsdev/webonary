<?php

namespace SIL\Tests\Webonary;

use SIL\Webonary\Reports;
use WP_UnitTestCase;

/**
 * @covers SIL\Webonary\Reports
 *
 * @noinspection PhpUndefinedNamespaceInspection
 */
class ReportsTest extends WP_UnitTestCase
{
	public function testReportRoutes()
	{
		$routes = Reports::ReportRoutes();

		$this->assertIsArray($routes);
		$this->assertEquals('Languages Used In Webonary', $routes[0]['title']);
	}

	public function testGetByID()
	{
		$report = Reports::GetByID('languages-used');
		$this->assertEquals('SIL\Webonary\Reports\LanguagesUsedInWebonary', $report);
	}

	public function testGetByID_Not_Exists()
	{
		$report = Reports::GetByID('bogus-report');
		$this->assertNull($report);
	}
}
