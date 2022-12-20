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
		$import = new Webonary_Pathway_Xhtml_Import();

		Webonary_Utility::sendAndContinue(function() {
			header('Content-Type: application/json');
			echo json_encode(array('result' => 'OK'));
		});

		$import->index_searchstrings();
	}

	public static function ajaxDisplaySites(): void
	{
		header('Content-Type: application/json');
		$data = ['data' => Webonary_Excel::GetAllSites(false)];
		echo json_encode($data);
		exit();
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
}
