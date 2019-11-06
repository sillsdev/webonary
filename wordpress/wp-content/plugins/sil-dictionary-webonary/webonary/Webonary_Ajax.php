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
		$x = array('indexed' => Webonary_Info::getCountIndexed());
		echo json_encode($x);
		exit();
	}
}
