<?php

namespace SIL\WebonaryCreateSite2\Controllers;

use SIL\WebonaryCreateSite2\Abstracts\NoticeType;
use SIL\WebonaryCreateSite2\Admin\Admin;
use SIL\WebonaryCreateSite2\Models\Applications;

class Copier
{
	public static function DoPage(): string
	{
		if (!current_user_can('manage_sites'))
			return '';

		$html = [];

		if (isset($_POST['remove']))
			self::RemoveApplication();

		if (isset($_GET['app-id']))
			$html[] = NewSite::DisplayNewSite($_GET['app-id']);
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

	public static function RemoveApplication(): void
	{
		$id1 = $_POST['remove'];
		$id2 = $_POST['app-id'];

		if ($id1 !== $id2) {
			Admin::AddAdminNotice(NoticeType::Warning, 'Application was NOT removed: invalid data received.');
			return;
		}

		$app = Applications::GetByID($id1);
		if (empty($app)) {
			Admin::AddAdminNotice(NoticeType::Warning, 'Application was NOT removed: application not found.');
			return;
		}

		$app->MarkRemoved();
		Admin::AddAdminNotice(NoticeType::Success, 'Application for ' . $app->LanguageName . ' has been removed.');
	}

	public static function AjaxCreateSite()
	{
		$app_id = $_POST['app-id'];
		if ($app_id == 'new') {

		}
		else {

		}

//		if ($id1 !== $id2) {
//			Admin::AddAdminNotice(NoticeType::Warning, 'New site was NOT created: invalid data received.');
//			return false;
//		}
//
//		echo json_encode($x);
//		exit();
	}
}
