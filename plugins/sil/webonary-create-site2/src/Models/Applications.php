<?php

namespace SIL\WebonaryCreateSite2\Models;

use wpdb;

class Applications
{
	/**
	 * @return Application[]
	 */
	public static function GetActiveApplications(): array
	{
		/** @var $wpdb wpdb */
		global $wpdb;

		$sql = <<<SQL
SELECT submit_time
FROM {$wpdb->prefix}cf7dbplugin_submits
WHERE field_name = 'newapplication'
SQL;
		$new_apps = $wpdb->get_results($sql);

		$sql = <<<SQL
SELECT field_name, field_value FROM {$wpdb->prefix}cf7dbplugin_submits
WHERE submit_time = %s
SQL;
		$return_val = [];

		foreach ($new_apps as $new_app) {
			$fields = $wpdb->get_results($wpdb->prepare($sql, $new_app->submit_time));
			$return_val[] = new Application($fields, $new_app->submit_time);
		}

		return $return_val;
	}

	public static function GetByID(string $app_id): ?Application
	{
		/** @var $wpdb wpdb */
		global $wpdb;

		$sql = <<<SQL
SELECT field_name, field_value FROM {$wpdb->prefix}cf7dbplugin_submits
WHERE submit_time = %s
SQL;
		$fields = $wpdb->get_results($wpdb->prepare($sql, $app_id));
		return new Application($fields, $app_id);
	}
}
