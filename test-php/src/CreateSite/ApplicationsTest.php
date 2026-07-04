<?php

namespace SIL\Tests\CreateSite;

use SIL\WebonaryCreateSite2\Models\Applications;
use WP_UnitTestCase;

/**
 * @covers SIL\WebonaryCreateSite2\Models\Application
 * @covers SIL\WebonaryCreateSite2\Models\Applications
 * @noinspection PhpUndefinedNamespaceInspection
 */
class ApplicationsTest extends WP_UnitTestCase
{
	public function testGetActiveApplications()
	{
		$apps = Applications::GetActiveApplications();

		$this->assertEquals([], $apps);
	}
}
