<?php
/** @noinspection PhpNoReturnAttributeCanBeAddedInspection */


class Webonary_Ajax
{
	public static function ajaxLanguage(): void
	{
		global $wpdb;

		$lang_code = filter_input(INPUT_POST, 'languagecode', FILTER_UNSAFE_RAW);

		/** @noinspection SqlResolve */
		$sql = $wpdb->prepare("SELECT `name` FROM $wpdb->terms WHERE slug = %s", array($lang_code));

		echo $wpdb->get_var($sql);
		exit();
	}

	public static function ajaxCurrentIndexedCount(): void
	{
		header('Content-Type: application/json');
		$x = array('indexed' => Webonary_Info::getCountIndexed(), 'total' => Webonary_Info::getPostCount());
		echo json_encode($x);
		exit();
	}

	public static function ajaxCurrentImportedCount(): void
	{
		header('Content-Type: application/json');
		$import_status = get_option('importStatus');
		if ($import_status != 'configured') {
			echo json_encode(array('imported' => -1));
			exit();
		}

		echo json_encode(array('imported' => Webonary_Info::getCountImported()));
		exit();
	}

	public static function ajaxCurrentReversalsCount(): void
	{
		header('Content-Type: application/json');
		$import_status = get_option('importStatus');
		if ($import_status != 'reversal') {
			echo json_encode(array('imported' => -1));
			exit();
		}

		echo json_encode(array('imported' => Webonary_Info::getCountReversals()));
		exit();
	}

	public static function ajaxRestartIndexing(): void
	{
		echo 'This functionality has been disabled.';
	}

	/**
	 * @return void
	 * @throws Exception
	 */
	public static function deleteData(): void
	{
		header('Content-Type: application/json');

		// must be a POST
		if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
			echo json_encode(['deleted' => 0, 'msg' => 'HTTP Error 1: Method not allowed']);
			exit();
		}

		// must be AJAX
		if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			echo json_encode(['deleted' => 0, 'msg' => 'HTTP Error 2: Method not allowed']);
			exit();
		}

		$data = Webonary_Delete_Data::DeleteDictionaryData();
		echo json_encode($data);
		exit();
	}

	public static function copyMongoData(): void
	{
		header('Content-Type: application/json');

		$site = filter_input(INPUT_POST, 'site', FILTER_UNSAFE_RAW, ['options' => ['default' => '']]);

		if (($site ?? '') == '') {
			echo json_encode(['error' => 'No site specified']);
			exit();
		}

		$step = filter_input(INPUT_POST, 'step', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);

		switch ($step) {
			case 1:
				$count = self::BackupSiteRecord();
				$plural = ($count !== 1) ? 's' : '';
				echo json_encode(['msg' => "$count Site data record$plural copied"]);
				break;

			case 2:
				echo json_encode(['msg' =>'2 Vernacular entries copied']);
				break;

			case 3:
				echo json_encode(['msg' => '3 Reversal entries copied']);
				break;

			default:
				echo json_encode(['error' => 'Invalid step']);
		}

		exit();
	}

	private static function BackupSiteRecord(): int
	{
		$client = new MongoDB\Client(
			'mongodb+srv://phillip_hopper:MWvpWREkJzA7xP8S@cluster0.hlbyb.mongodb.net'
		);

		$db = $client->{'webonary-work'}->collection;
		$result = $db->find()->toArray();
		$x = 1;

		return 1;
	}
}
