<?php


class Webonary_Filters
{
	/**
	 * @param string $value_name
	 * @return string[]
	 */
	public static function PostArray($value_name)
	{
		return filter_input(INPUT_POST, $value_name, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
	}
}
