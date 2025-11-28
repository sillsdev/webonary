<?php

namespace SIL\Tests\Mocks;

use SIL\Webonary\Helpers\Request;

class MockRequest
{
	/**
	 * Call MockRequest::Init() in the setup function for the unit test.
	 *
	 * @return void
	 */
	public static function Init(): void
	{
		Request::$FilterCallable = 'SIL\Tests\Mocks\MockFilterInput::FilterInput';

		$_POST = [];
		$_GET = [];
		$_SERVER = $_SERVER ?? [];

		$_SERVER['REQUEST_METHOD'] = 'GET';

		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']))
			unset($_SERVER['HTTP_X_REQUESTED_WITH']);
	}
}
