<?php

namespace SIL\Tests\CreateSite;

use SIL\WebonaryCreateSite2\Abstracts\NoticeType;
use SIL\WebonaryCreateSite2\Admin\Admin;
use WP_UnitTestCase;

/**
 * @covers SIL\WebonaryCreateSite2\Admin\Admin
 * @covers SIL\WebonaryCreateSite2\Admin\AdminNotice
 * @noinspection PhpUndefinedNamespaceInspection
 */
class AdminTest extends WP_UnitTestCase
{
	public function testAdminNoticeSuccess()
	{
		Admin::AddAdminNotice(NoticeType::Success, 'Unit test success message');
		$html = Admin::DoAdminNotices();
		$this->assertStringContainsString('<p>Unit test success message</p>', $html);
		$this->assertStringContainsString('<div class="notice notice-success is-dismissible">', $html);
	}

	public function testAdminNoticeError()
	{
		Admin::AddAdminNotice(NoticeType::Error, 'Unit test error message');
		$html = Admin::DoAdminNotices();
		$this->assertStringContainsString('<p>Unit test error message</p>', $html);
		$this->assertStringContainsString('<div class="notice notice-error is-dismissible">', $html);
	}
}
