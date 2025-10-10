<?php


use JetBrains\PhpStorm\NoReturn;

class Webonary_Ajax
{
	#[NoReturn] public static function ajaxLanguage(): void
	{
		global $wpdb;

		$lang_code = filter_input(INPUT_POST, 'languagecode', FILTER_UNSAFE_RAW);

		/** @noinspection SqlResolve */
		$sql = $wpdb->prepare("SELECT `name` FROM $wpdb->terms WHERE slug = %s", array($lang_code));

		echo $wpdb->get_var($sql);
		exit();
	}

	#[NoReturn] public static function ajaxCurrentIndexedCount(): void
	{
		header('Content-Type: application/json');
		$x = array('indexed' => Webonary_Info::getCountIndexed(), 'total' => Webonary_Info::getPostCount());
		echo json_encode($x);
		exit();
	}

	#[NoReturn] public static function ajaxCurrentImportedCount(): void
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

	#[NoReturn] public static function ajaxCurrentReversalsCount(): void
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
	#[NoReturn] public static function deleteData(): void
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

	#[NoReturn] public static function copyMongoData(): void
	{
		set_time_limit(600);

		header('Content-Type: application/json');

		$site = filter_input(INPUT_POST, 'site', FILTER_UNSAFE_RAW, ['options' => ['default' => '']]);

		if (($site ?? '') == '') {
			echo json_encode(['error' => 'No site specified']);
			exit();
		}

		$step = filter_input(INPUT_POST, 'step', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);

		switch ($step) {
			case 1:
				self::CopySiteRecord();
				echo json_encode(['msg' => 'Site data record copied']);
				break;

			case 2:
				$count = self::CopyVernacularTable();
				$plural = ($count !== 1) ? 'ies' : 'y';
				echo json_encode(['msg' =>"$count Vernacular entr$plural copied"]);
				break;

			case 3:
				self::CreateVernacularIndexes();
				echo json_encode(['msg' =>'Vernacular indexes created']);
				break;

			case 4:
				$count = self::CopyReversalEntries();
				$plural = ($count !== 1) ? 'ies' : 'y';
				echo json_encode(['msg' =>"$count Reversal entr$plural copied"]);
				break;

			default:
				echo json_encode(['error' => 'Invalid step']);
		}

		exit();
	}

	/** @noinspection PhpUndefinedFieldInspection */
	private static function CopySiteRecord(): void
	{
		$dictionary_id = Webonary_Cloud::getBlogDictionaryId();

		$live_db = Webonary_Cloud::GetMongoDbConnection(true);
		$dictionary_doc = $live_db->webonaryDictionaries->findOne(
			['_id' => $dictionary_id]
		);

		$work_db = Webonary_Cloud::GetMongoDbConnection();
		$work_db->webonaryDictionaries->replaceOne(
			['_id' => $dictionary_id],
			$dictionary_doc,
			['upsert' => true]
		);
	}

	private static function CopyVernacularTable(): int
	{
		$dictionary_id = Webonary_Cloud::getBlogDictionaryId();
		$collection_name = 'webonaryEntries_' . $dictionary_id;

		$work_db = Webonary_Cloud::GetMongoDbConnection();
		$work_db->$collection_name->drop();
		$work_db->createCollection($collection_name);

		$live_db = Webonary_Cloud::GetMongoDbConnection(true);
		$docs = $live_db->$collection_name->find();

		// write 50 at a time
		$docs_to_insert = [];
		$num_inserted = 0;
		foreach ($docs as $doc) {

			$docs_to_insert[] = $doc;
			$num_inserted++;

			if (count($docs_to_insert) > 49) {
				$work_db->$collection_name->insertMany($docs_to_insert);
				$docs_to_insert = [];
			}
		}

		if (!empty($docs_to_insert)) {
			$work_db->$collection_name->insertMany($docs_to_insert);
			unset($docs_to_insert);
		}

		return $num_inserted;
	}

	private static function CreateVernacularIndexes(): void
	{
		$dictionary_id = Webonary_Cloud::getBlogDictionaryId();
		$collection_name = 'webonaryEntries_' . $dictionary_id;

		$work_db = Webonary_Cloud::GetMongoDbConnection();
		$work_db->$collection_name->createIndex(
			[
				'letterHead' => 1,
				'sortIndex' => 1
			]
		);

		$work_db->$collection_name->createIndex(
			['senses.semanticdomains.name.valuex' => 1],
			[
				'collation' => [
					'locale' => 'vi',
					'strength' => 2
				]
			]
		);

		$work_db->$collection_name->createIndex(
			[
				'mainheadword.value' => 'text',
				'citationform.value' => 'text',
				'lexemeform.value' => 'text',
				'headword.value' => 'text',
				'senses.definitionorgloss.value' => 'text',
				'senses.definition.value' => 'text',
				'senses.gloss.value' => 'text',
				'searchTexts' => 'text',
			],
			[
				'weights' => [
					'mainheadword.value' => 50,
					'citationform.value' => 40,
					'lexemeform.value' => 30,
					'headword.value' => 20,
					'senses.definitionorgloss.value' => 10,
					'senses.definition.value' => 10,
					'senses.gloss.value' => 10,
				]
			]
		);
	}

	/** @noinspection PhpUndefinedFieldInspection */
	private static function CopyReversalEntries(): int
	{
		// first remove old reversal entries
		$dictionary_id = Webonary_Cloud::getBlogDictionaryId();
		$work_db = Webonary_Cloud::GetMongoDbConnection();
		$work_db->webonaryReversals->deleteMany(['dictionaryId' => $dictionary_id]);

		$live_db = Webonary_Cloud::GetMongoDbConnection(true);
		$docs = $live_db->webonaryReversals->find(['dictionaryId' => $dictionary_id]);

		// write 50 at a time
		$docs_to_insert = [];
		$num_inserted = 0;
		foreach ($docs as $doc) {

			$docs_to_insert[] = $doc;
			$num_inserted++;

			if (count($docs_to_insert) > 49) {
				$work_db->webonaryReversals->insertMany($docs_to_insert);
				$docs_to_insert = [];
			}
		}

		if (!empty($docs_to_insert)) {
			$work_db->webonaryReversals->insertMany($docs_to_insert);
			unset($docs_to_insert);
		}

		return $num_inserted;
	}
}
