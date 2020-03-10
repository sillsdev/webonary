<?php


class Webonary_Ajax
{
	public static function ajaxLanguage()
	{
		global $wpdb;

		$lang_code = filter_input(INPUT_POST, 'languagecode', FILTER_UNSAFE_RAW);

		/** @noinspection SqlResolve */
		$sql = $wpdb->prepare("SELECT `name` FROM {$wpdb->terms} WHERE slug = %s", array($lang_code));

		echo $wpdb->get_var($sql);
		exit();
	}

	public static function ajaxCurrentIndexedCount()
	{
		header('Content-Type: application/json');
		$x = array('indexed' => Webonary_Info::getCountIndexed(), 'total' => Webonary_Info::getPostCount());
		echo json_encode($x);
		exit();
	}

	public static function ajaxCurrentImportedCount()
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

	public static function ajaxCurrentReversalsCount()
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

	public static function ajaxRestartIndexing()
	{
		$import = new Webonary_Pathway_Xhtml_Import();

		Webonary_Utility::sendAndContinue(function() {
			header('Content-Type: application/json');
			echo json_encode(array('result' => 'OK'));
		});

		$import->index_searchstrings();
	}
}
