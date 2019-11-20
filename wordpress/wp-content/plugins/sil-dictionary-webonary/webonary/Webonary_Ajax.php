<?php


class Webonary_Ajax
{
	public static function ajaxLanguage()
	{
		global $wpdb;

		$lang_code = filter_input(INPUT_POST, 'languagecode', FILTER_UNSAFE_RAW);

		/** @noinspection SqlResolve */
		$sql = $wpdb->prepare("SELECT `name` FROM {$wpdb->terms} WHERE slug = %s", array($lang_code));

		echo $wpdb->get_var($sql);;
		exit();
	}

	public static function ajaxCurrentIndexedCount()
	{
		header('Content-Type: application/json');
		$x = array('indexed' => Webonary_Info::getCountIndexed(), 'total' => Webonary_Info::getCountImported());
		echo json_encode($x);
		exit();
	}

	public static function ajaxCurrentImportedCount()
	{
		$import_status = get_option('importStatus');
		if ($import_status != 'configured') {
			echo json_encode(array('imported' => -1));
			exit();
		}

		echo json_encode(array('imported' => Webonary_Info::getCountImported()));
		exit();
	}
}
