<?php

namespace SIL\Tests\Webonary;

use SIL\Tests\Mocks\MockRequest;
use SIL\Webonary\AdminWidget;
use WP_UnitTestCase;

/**
 * @covers SIL\Webonary\AdminWidget
 * @covers SIL\Webonary\Admin
 * @covers SIL\Webonary\AdminReportTable
 * @covers SIL\Webonary\Reports
 * @covers SIL\Webonary\Attributes\Report
 * @covers SIL\Webonary\Abstracts\AdminReportTrait
 * @covers SIL\Webonary\Reports\LanguagesUsedInWebonary
 *
 * @noinspection PhpUndefinedNamespaceInspection
 */
class AdminWidgetTest extends WP_UnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		MockRequest::Init();
	}

	public function testShowWidget()
	{
		$html = AdminWidget::ShowWidget();
		$this->assertStringContainsString('<h1>Webonary Admin Tools</h1>', $html);
	}

	public function testShowReports()
	{
		$html = AdminWidget::ShowReports();
		$this->assertStringContainsString('<h1>Webonary Reports</h1>', $html);
		$this->assertStringContainsString('Languages Used In Webonary', $html);
	}

	public function testShowReports_Bogus()
	{
		$_GET['report-id'] = 'bogus';

		$html = AdminWidget::ShowReports();
		$this->assertStringContainsString('<h1>Webonary Reports</h1>', $html);
		$this->assertStringContainsString('Languages Used In Webonary', $html);
	}
}
