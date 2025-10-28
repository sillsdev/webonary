<?php


class Webonary_Filters
{
	/**
	 * @param string $value_name
	 * @return array|bool|null
	 */
	public static function PostArray(string $value_name): array|bool|null
	{
		return filter_input(INPUT_POST, $value_name, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
	}
}
