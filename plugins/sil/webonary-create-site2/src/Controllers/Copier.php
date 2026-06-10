<?php

namespace SIL\WebonaryCreateSite2\Controllers;

use SIL\WebonaryCreateSite2\Admin\Admin;

class Copier
{
	public static function DoPage(): string
	{
		if (!current_user_can('manage_sites'))
			return '';

		$html = [];

		if (isset($_GET['app_id']))
			$html[] = NewSite::DisplayNewSite($_GET['app_id']);
		else
			$html[] = ApplicationList::DisplayApplicationList();

		$notices = Admin::DoAdminNotices();

		if ($notices)
			array_unshift($html, $notices);

		$html = array_filter($html);
		$return_val = implode(PHP_EOL, $html);

		if (!defined('PHP_UNIT'))
			echo $return_val;

		return $return_val;
	}

}
