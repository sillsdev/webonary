<?php


class Webonary_Db
{
	/**
	 * @param string $query
	 * @param $args,...
	 *
	 * @return bool
	 */
	public static function GetBool(string $query, $args): bool
	{
		/** @var wpdb $wpdb */
		global $wpdb;

		$all_args = func_get_args();

		if (count($all_args) == 1) {
			$sql = $query;
		}
		else {
			array_shift($all_args);
			$sql = $wpdb->prepare($query, $all_args);
		}

		$val = $wpdb->get_var($sql);

		return !empty($val);
	}
}
