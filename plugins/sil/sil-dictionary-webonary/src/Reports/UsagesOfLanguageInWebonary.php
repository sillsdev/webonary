<?php
/** @noinspection PhpUnused */

namespace SIL\Webonary\Reports;

use SIL\Webonary\Abstracts\AdminReportTrait;
use SIL\Webonary\Attributes\Report;
use SIL\Webonary\Helpers\Request;
use SIL\Webonary\Mongo;
use WP_List_Table;

#[Report(
	slug: 'uses-of-language',
	title: 'Uses Of Language In Webonary',
	show_in_list: false
)]
class UsagesOfLanguageInWebonary extends WP_List_Table
{
	use AdminReportTrait;

	public static function GetReportTitle(): string
	{
		return self::GetAttributes()['title'] . ': ' . Request::GetStr('lang-name');
	}

	protected function GetReportData(): array
	{
		$return_val = [];
		$db = Mongo::GetMongoDB();

		$lang_code = Request::GetStr('lang-code');

		/** @noinspection PhpUndefinedFieldInspection */
		$collection = $db->webonaryDictionaries;

		// get dictionaries with this main language
		$found = $collection->find(
			['mainLanguage.lang' => $lang_code],
			['projection' => ['_id' => 1]]
		)->toArray();

		foreach ($found as $entry) {
			$return_val[] = ['dictionary_id' => $entry['_id'], 'field' => 'Main Language'];
		}

		// get dictionaries with this reversal language
		$found = $collection->find(
			['reversalLanguages.lang' => $lang_code],
			['projection' => ['_id' => 1]]
		)->toArray();

		foreach ($found as $entry) {
			$return_val[] = ['dictionary_id' => $entry['_id'], 'field' => 'Reversal Language'];
		}

		sort($return_val);

		return $return_val;
	}

	public function get_columns(): array
	{
		return [
			'dictionary_id' => 'Dictionary',
			'field' => 'Field'
		];
	}

	public function get_sortable_columns(): array
	{
		return [
			'dictionary_id' => ['dictionary_id', false],
			'field' => ['field', false]
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
	 * Formats the dictionary_id column as a hyperlink
	 * @param $item
	 * @return string
	 * @noinspection PhpUnused
	 */
	protected function column_dictionary_id($item): string
	{
		$dictionary_id = $item["dictionary_id"];
		$url = '/' . $dictionary_id;
		return <<<HTML
<a href="$url" title="" target="_blank">$dictionary_id</a>
HTML;
	}
}
