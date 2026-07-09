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
			$notice = __('Application was NOT removed: invalid data received.', 'webonary-create-site2');
			Admin::AddAdminNotice(NoticeType::Warning, $notice);
			return;
		}

		$app = Applications::GetByID($id1);
		if (empty($app)) {
			$notice = __('Application was NOT removed: application not found.', 'webonary-create-site2');
			Admin::AddAdminNotice(NoticeType::Warning, $notice);
			return;
		}

		$app->MarkRemoved();
		$notice = sprintf(__('Application for %s has been removed.', 'webonary-create-site2'), $app->LanguageName);
		Admin::AddAdminNotice(NoticeType::Success, $notice);
	}

	public static function AjaxCreateSite()
	{
		$app_id = $_POST['app-id'];
		if ($app_id == 'new') {

		}
		else {

		}

		$data = ['status' => 'OK'];
//		if ($id1 !== $id2) {
//			Admin::AddAdminNotice(NoticeType::Warning, 'New site was NOT created: invalid data received.');
//			return false;
//		}

		echo json_encode($data);
		exit();
	}
}
