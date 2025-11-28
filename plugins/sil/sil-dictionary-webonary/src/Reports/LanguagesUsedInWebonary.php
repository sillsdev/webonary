<?php
/** @noinspection PhpUnused */

namespace SIL\Webonary\Reports;

use SIL\Webonary\Abstracts\AdminReportTrait;
use SIL\Webonary\Attributes\Report;
use SIL\Webonary\Helpers\Request;
use SIL\Webonary\Mongo;
use WP_List_Table;

#[Report(
	slug: 'languages-used',
	title: 'Languages Used In Webonary',
	show_in_list: true
)]
class LanguagesUsedInWebonary extends WP_List_Table
{
	use AdminReportTrait;

	protected function GetReportData(): array
	{
		$db = Mongo::GetMongoDB();

		$order_by = Request::GetStr('orderby', 'lang_code') == 'lang_code' ? 'lang_code' : 'lang_name';
		$sort_dir = Request::GetStr('order', 'asc') == 'asc' ? 1 : -1;

		/** @noinspection PhpUndefinedFieldInspection */
		$collection = $db->webonaryDictionaries;

		$return_val = $collection->aggregate(
			[
				['$unwind' => '$mainLanguage'],
				['$replaceRoot' => ['newRoot' => '$mainLanguage']],
				['$match' => ['lang' => ['$ne' => '']]],
				['$project' => [
					'_id' => 0,
					'lang' => 1,
					'title' => 1
				]],
				['$unionWith' => [
					'coll' => 'webonaryDictionaries',
					'pipeline' => [
						['$unwind' => '$reversalLanguages'],
						['$replaceRoot' => ['newRoot' => '$reversalLanguages']],
						['$match' => ['lang' => ['$ne' => '']]],
						['$project' => [
							'_id' => 0,
							'lang' => 1,
							'title' => 1
						]],
						['$sort' => ['_id' => 1]]
					]
				]],
				['$group' => [
					'_id' => '$lang',
					'lang_name' => ['$min' => '$title']
				]],
				['$sort' => ['_id' => 1]],
				['$project' => [
					'_id' => 0,
					'lang_code' => '$_id',
					'lang_name' => 1
				]]
			]
		)->toArray();

		// clean up the language names a bit
		foreach ($return_val as $entry) {

			$name = null;
			$description = null;
			$code = $entry['lang_code'];

			// skip private use codes
			if (!str_contains($code, '-x-')) {
				$name = locale_get_display_language($code, 'en');
				$description = locale_get_display_name($code, 'en');
			}

			if (isset($description) && $description != $code)
				$entry['lang_name'] = $description;
			elseif (isset($name) && $name != $code)
				$entry['lang_name'] = $name;
		}

		usort($return_val, function ($a, $b) use ($order_by, $sort_dir) {

			if ($sort_dir == 1)
				return strtolower($a[$order_by]) <=> strtolower($b[$order_by]);

			return strtolower($b[$order_by]) <=> strtolower($a[$order_by]);
		});

		return $return_val;
	}

	public function get_columns(): array
	{
		return [
			'lang_code' => 'Code',
			'lang_name' => 'Name'
		];
	}

	public function get_sortable_columns(): array
	{
		return [
			'lang_code' => ['lang_code', false],
			'lang_name' => ['lang_name', false]
		];
	}

	public function prepare_items(): void
	{
		$data = $this->GetReportData();

		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $data;
	}

	/**
	 * Formats the lang_code column as a hyperlink
	 * @param $item
	 * @return string
	 * @noinspection PhpUnused
	 */
	protected function column_lang_code($item): string
	{
		$lang_code = $item["lang_code"];
		$url = admin_url('admin.php?page=webonary-reports') . '&report-id=uses-of-language&lang-code=' . $lang_code . '&lang-name=' . urlencode($item['lang_name']);
		return <<<HTML
<a href="$url" title="">$lang_code</a>
HTML;
	}
}
