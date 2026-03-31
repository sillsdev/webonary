<?php
/** @noinspection PhpUnused */

namespace SIL\Webonary\Reports;

use MongoDB\Model\BSONDocument;
use SIL\Webonary\Abstracts\AdminReportTrait;
use SIL\Webonary\Attributes\Report;
use SIL\Webonary\Mongo;
use Webonary_Utility;
use WP_List_Table;

#[Report(
	slug: 'display-all-sites',
	title: 'Display All Sites',
	show_in_list: true
)]
class DisplayAllSites extends WP_List_Table
{
	use AdminReportTrait;

	protected function GetReportData(): array
	{
		global $wpdb;

		$rows = [];

		$sql = <<<SQL
SELECT blog_id, domain, DATE_FORMAT(registered, '%Y-%m-%d') AS registered
FROM $wpdb->blogs
WHERE blog_id != $wpdb->blogid
  AND site_id = '$wpdb->siteid'
  AND spam = '0'
  AND deleted = '0'
  AND archived = '0'
ORDER BY registered DESC
SQL;
		$blogs = $wpdb->get_results($sql, ARRAY_A);

		if (count($blogs) == 0)
			return $rows;

		$db = Mongo::GetMongoDB();

		/** @var BSONDocument[] $last_updated */
		/** @noinspection PhpUndefinedFieldInspection */
		$last_updated = $db->webonaryDictionaries->find(
			[],
			['projection' => ['_id' => 1, 'updatedAt' => 1]]
		)->toArray();

		foreach ($blogs as $blog)  {

			switch_to_blog($blog['blog_id']);

			$blog_details = get_blog_details($blog['blog_id']);
			$fields = [];

			$domain_path = $blog_details->domain . $blog_details->path;

			preg_match_all('~[:en]](.+?)\[~', $blog_details->blogname, $blog_name_matches);
			if(count($blog_name_matches[0]) > 0)
				$fields['SiteTitle'] = trim($blog_name_matches[1][0]);
			else
				$fields['SiteTitle'] = trim($blog_details->blogname);

			$fields['Country'] = preg_replace('/,\s*/', ',<br>', get_option('countryName'));
			$fields['Region'] = get_option('regionName');

			$fields['URL'] = 'https://' . $domain_path;

			$copyright_holder = Webonary_Utility::GetCopyright();

			// split on the copyright symbol, remove the trademark symbol
			$arr_footer = explode('©', str_replace('®', '', $copyright_holder), 2);

			if (count($arr_footer) > 1) {

				// take the part to the right of the copyright symbol
				$copyright = $arr_footer[1];

				// remove the 4-digit year
				$copyright = preg_replace('/\d{4}/', '', $copyright);

				// remove the year short tag
				$copyright = str_replace('[year]', '', $copyright);

				$fields['Copyright'] = trim($copyright);
			}
			else {
				$fields['Copyright'] = trim($copyright_holder);
			}

			$sql = <<<SQL
SELECT REPLACE(meta_value, 'https://www.ethnologue.com/language/','') AS ethnologue_code
FROM wp_{$blog['blog_id']}_postmeta
WHERE meta_key = '_menu_item_url'
  AND meta_value LIKE '%ethnologue%'
SQL;
			$ethnologue_code = trim($wpdb->get_var($sql));
			$fields['Code'] = $ethnologue_code;

			if (get_option('useCloudBackend')) {
				$fields['Backend'] = 'Cloud';

				$dictionary_id = str_replace('/', '', $blog_details->path);

				$collection_name = 'webonaryEntries_' . $dictionary_id;
				$num_posts = $db->$collection_name->countDocuments() ?? '';

				$last_updated_row = array_find($last_updated, function ($row) use ($dictionary_id) {
					return $row['_id'] == $dictionary_id;
				});

				if (empty($last_updated_row))
					$last_edit_date = '';
				elseif (gettype($last_updated_row->updatedAt) == 'string')
					$last_edit_date = date("Y-m-d H:m:s", strtotime($last_updated_row->updatedAt));
				else
					$last_edit_date = $last_updated_row->updatedAt->toDateTime()->format('Y-m-d H:m:s');
			}
			else {
				$fields['Backend'] = 'Wordpress';

				$num_posts = $wpdb->get_var("SELECT COUNT(*) FROM wp_{$blog ['blog_id']}_posts WHERE post_status = 'publish' AND post_type = 'post'");
				$last_edit_date = $wpdb->get_var("SELECT post_date FROM wp_{$blog ['blog_id']}_posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date DESC");
			}

			$fields['Entries'] = $num_posts;
			$fields['CreateDate'] = $this->NonBreakingDate($blog['registered']);

			/** @noinspection SqlResolve */
			$published_date = $wpdb->get_var("SELECT DATE_FORMAT(link_updated, '%Y-%m-%d') AS link_updated FROM wp_links WHERE link_url LIKE '%$domain_path%'");
			$fields['PublishDate'] = $this->NonBreakingDate($published_date);

			$fields['ContactEmail'] = get_option('admin_email');

			if (empty($last_edit_date))
				$fields['LastUpload'] = '';
			elseif ($last_edit_date == '0000-00-00 00:00:00')
				$fields['LastUpload'] = '';
			else
				$fields['LastUpload'] = $this->NonBreakingDate($last_edit_date);

			$notes = stripslashes(get_option('notes'));
			if ($this->is_excel)
				$fields['Notes'] = $notes;
			else
				$fields['Notes'] = '<div class="min-width-200">' . $notes . '</div>';

			$mapped = array_map(function($a) {

				if (is_null($a))
					return '';

				$s = preg_replace('/<script.+<\/script>/s', '', $a);
				$s = preg_replace('/[\r\n\t]/', ',', $s);
				return htmlspecialchars_decode(str_replace('<sup></sup>', '', $s), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);
			}, $fields);

			$rows[] = $mapped;
		}

		restore_current_blog();

		return $rows;
	}

	public function get_columns(): array
	{
		return [
			'SiteTitle' => 'Site Title',
			'Country' => 'Country',
			'Region' => 'Region',
			'URL' => 'URL',
			'Copyright' => 'Copyright',
			'Code' => 'Code',
			'Backend' => 'Backend',
			'Entries' => 'Entries',
			'CreateDate' => 'Create Date',
			'PublishDate' => 'Publish Date',
			'ContactEmail' => 'Contact Email',
			'LastUpload' => 'Last Upload',
			'Notes' => 'Notes'
		];
	}

	public function get_sortable_columns(): array
	{
		return [
			'SiteTitle' => ['SiteTitle', false],
			'Country' => ['Country', false],
			'Region' => ['Region', false],
			'URL' => ['URL', false],
			'Copyright' => ['Copyright', false],
			'Code' => ['Code', false],
			'Backend' => ['Backend', false],
			'Entries' => ['Entries', false],
			'CreateDate' => ['CreateDate', false],
			'PublishDate' => ['PublishDate', false],
			'ContactEmail' => ['ContactEmail', false],
			'LastUpload' => ['LastUpload', false],
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
}
