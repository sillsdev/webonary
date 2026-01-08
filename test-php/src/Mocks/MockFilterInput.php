<?php

namespace SIL\Tests\Mocks;

class MockFilterInput
{
	public static function FilterInput($type, $variable_name, int $filter=FILTER_DEFAULT, $options=null): mixed
	{
		$input = null;

		switch ($type) {
			case INPUT_GET:
				$input = $_GET;
				break;

			case INPUT_POST:
				$input = $_POST;
				break;

			case INPUT_SERVER:
				$input = $_SERVER;
				break;
		}

		return filter_var($input[$variable_name] ?? '', $filter, $options ?? 0);
	}
}
