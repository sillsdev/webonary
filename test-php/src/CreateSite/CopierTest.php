<?php
/** @noinspection PhpArrayWriteIsNotUsedInspection */

namespace SIL\Tests\CreateSite;

use SIL\WebonaryCreateSite2\Admin\Admin;
use SIL\WebonaryCreateSite2\Controllers\Copier;
use WP_UnitTestCase;

/**
 * @covers SIL\WebonaryCreateSite2\Controllers\Copier
 * @noinspection PhpUndefinedNamespaceInspection
 */
class CopierTest extends WP_UnitTestCase
{
	public function testRemoveApplication_MissingData()
	{
		$_POST = ['remove' => '1'];
		Copier::RemoveApplication();
		$html = Admin::DoAdminNotices();
		$this->assertStringContainsString('<p>Application was NOT removed: invalid data received.</p>', $html);
	}

	public function testRemoveApplication_InvalidData()
	{
		$_POST = [
			'remove' => '1',
			'app-id' => '2'
		];
		Copier::RemoveApplication();
		$html = Admin::DoAdminNotices();
		$this->assertStringContainsString('<p>Application was NOT removed: invalid data received.</p>', $html);
	}

	public function testRemoveApplication_BadID()
	{
		$_POST = [
			'remove' => '12345',
			'app-id' => '12345'
		];
		Copier::RemoveApplication();
		$html = Admin::DoAdminNotices();
		$this->assertStringContainsString('<p>Application was NOT removed: application not found.</p>', $html);
	}

	public function testRemoveApplication()
	{
		$_POST = [
			'remove' => '1234567890.1234',
			'app-id' => '1234567890.1234'
		];
		Copier::RemoveApplication();
		$html = Admin::DoAdminNotices();
		$this->assertStringContainsString('<p>Application for Unit Test Lang has been removed.</p>', $html);
	}
}
