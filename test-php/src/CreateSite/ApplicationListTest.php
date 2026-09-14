<?php

namespace SIL\Tests\CreateSite;

use SIL\WebonaryCreateSite2\Controllers\ApplicationList;
use WP_UnitTestCase;

class ApplicationListTest extends WP_UnitTestCase
{
	public function testDisplayApplicationList()
	{
		$html = ApplicationList::DisplayApplicationList();
		$this->assertStringContainsString('<button type="submit" name="remove" value="1234567890.1234"', $html);
	}
}
