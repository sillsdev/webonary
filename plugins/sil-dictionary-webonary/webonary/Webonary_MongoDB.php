<?php

use MongoDB\Client;
use MongoDB\Database;
use MongoDB\Driver\ServerApi;
use MongoDB\Model\BSONDocument;

class Webonary_MongoDB
{
	private static ?Database $database = null;
	private static ?string $sem_domain_abbrev_field = null;
	private static ?string $site_collection_name = null;

	private const DB_COLLATION_LOCALES = [
		'af',
		'sq',
		'am',
		'ar',
		'hy',
		'as',
		'az',
		'be',
		'bn',
		'bs',
		'bg',
		'my',
		'ca',
		'chr',
		'zh',
		'zh_Hant',
		'hr',
		'cs',
		'da',
		'nl',
		'dz',
		'en',
		'en_US',
		'en_US_POSIX',
		'eo',
		'et',
		'ee',
		'fo',
		'fil',
		'fr',
		'fr_CA',
		'gl',
		'ka',
		'de',
		'de_AT',
		'el',
		'gu',
		'ha',
		'haw',
		'he',
		'hi',
		'hu',
		'is',
		'ig',
		'smn',
		'id',
		'ga',
		'it',
		'ja',
		'kl',
		'kn',
		'kk',
		'kok',
		'ko',
		'ky',
		'lkt',
		'lo',
		'lv',
		'ln',
		'lt',
		'dsb',
		'lb',
		'mk',
		'ms',
		'ml',
		'mt',
		'mr',
		'mn',
		'ne',
		'se',
		'nb',
		'nn',
		'or',
		'om',
		'ps',
		'fa',
		'fa_AF',
		'pl',
		'pt',
		'pa',
		'ro',
		'ru',
		'sr',
		'sr_Latn',
		'si',
		'sk',
		'sl',
		'es',
		'sw',
		'sv',
		'ta',
		'te',
		'th',
		'bo',
		'to',
		'tr',
		'uk',
		'hsb',
		'ur',
		'ug',
		'vi',
		'wae',
		'cy',
		'yi',
		'yo',
		'zu',
	];
	private const STRENGTH_CI_AI = 1;
	private const STRENGTH_CI_AS = 2;
	private const STRENGTH_CS_AS = 3;

	/**
	 * @return Database
	 * @noinspection DuplicatedCode
	 */
	public static function GetMongoDB(): Database
	{
		if (!is_null(self::$database))
			return self::$database;

		$settings = WEBONARY_MONGO;
		$catalog = $settings['cat'];

		$uri = "mongodb+srv://{$settings['usr']}:{$settings['pwd']}@{$settings['url']}/?retryWrites=true&w=majority&appName=Cluster0";

		// set the version of the Stable API on the client
		$api_version = new ServerApi(ServerApi::V1);

		// create a new client and connect to the server
		$client = new Client($uri, [], ['serverApi' => $api_version]);

		self::$database = $client->$catalog;

		return self::$database;
	}

	private static function GetSiteCollectionName(): string
	{
		if (is_null(self::$site_collection_name))
			self::$site_collection_name = 'webonaryEntries_' . Webonary_Cloud::getBlogDictionaryId();

		return self::$site_collection_name;
	}

	private static function GetSemDomainAbbrevField(): string
	{
		if (!is_null(self::$sem_domain_abbrev_field))
			return self::$sem_domain_abbrev_field;

		$found = get_option('mongo_sem_domain');

		if (!empty($found['value']) && $found['expires'] > time()) {
			self::$sem_domain_abbrev_field = $found['value'];
			return self::$sem_domain_abbrev_field;
		}

		$db = self::GetMongoDB();
		$collection = self::GetSiteCollectionName();

		/** @var BSONDocument $doc */
		$doc = $db->$collection->findOne(
			['senses.semanticdomains' =>
				[
					'$ne' => null
				]
			],
			['projection' => ['_id' => 1, 'senses.semanticdomains' => 1]]
		);

		$keys = array_keys((array)$doc->senses[0]->semanticdomains[0]);
		$abbrev = array_find($keys, function($val) { return str_starts_with($val, 'abbrev'); });
		self::$sem_domain_abbrev_field = $abbrev;

		// remember this for 24 hours
		update_option('mongo_sem_domain', ['value' => $abbrev, 'expires' => time() + (60 * 60 * 24)]);

		return self::$sem_domain_abbrev_field;
	}

	/**
	 * @param string $sem_domain_text
	 * @param string $sem_domain_code
	 * @param int $page_num
	 * @param int $posts_per_page
	 * @return array
	 * @throws Exception
	 */
	public static function DoSemanticDomainSearch(string $sem_domain_text, string $sem_domain_code, int $page_num, int $posts_per_page): array
	{
		$db = self::GetMongoDB();
		$collection = self::GetSiteCollectionName();

		// 'senses.semanticdomains.name.value'
		if (empty($sem_domain_code)) {
			$find = ['senses.semanticdomains.name.value' => $sem_domain_text];
		}
		else {
			$find = [
				'senses.semanticdomains.' . self::GetSemDomainAbbrevField() . '.value' =>
					[
						'$in' => [
							$sem_domain_code,
							new MongoDB\BSON\Regex('^' . preg_quote($sem_domain_code, '/'), 'i')
						]
					]
			];
		}

		$count = $db->$collection->countDocuments($find);
		$posts = [];

		/** @var BSONDocument[] $docs */
		$docs = $db->$collection->find(
			$find,
			[
				'projection' => [
					'_id' => 1,
					'dictionaryId' => 1,
					'guid' => 1,
					'updatedAt' => 1,
					'mainheadword' => 1,
					'displayXhtml' => 1
				],
				'limit' => $posts_per_page,
				'skip' => $posts_per_page * ($page_num - 1),
//				'collation' => [
//					'locale' => self::GetLocale($lang_code),
//					'strength' => self::STRENGTH_CI_AI
//				]
			]
		)->toArray();

		foreach (json_decode(json_encode($docs), false) as $key => $entry) {
			if (Webonary_Cloud::isValidEntry($entry)) {
				$post = Webonary_Cloud::entryToFakePost($entry);
				$posts[$key] = $post;
			}
		}
		return [
			$count,
			$posts
		];
	}

	private static function GetLocale($lang_code): string
	{
		return in_array($lang_code, self::DB_COLLATION_LOCALES) ? $lang_code : 'vi';
	}
}
