<?php

namespace SIL\Tests\CreateSite;

use SIL\WebonaryCreateSite2\Main;
use WP_UnitTestCase;

/**
 * @covers SIL\WebonaryCreateSite2\Main
 * @noinspection PhpUndefinedNamespaceInspection
 */
class MainTest extends WP_UnitTestCase
{
	public function testSetHooks_Admin()
	{
		set_current_screen('dashboard');
		$this->assertEquals(3, Main::Run());
	}
}
