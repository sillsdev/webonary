<?php

use MongoDB\Client;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class WebonaryHome_Ajax
{
	private static array $regular_style = [
		'font' => [
			'bold' => false,
			'color' => ['rgb' => '000000'],
			'size' => 12,
			'name' => 'Arial'
		],
		'fill' => [
			'fillType' => Fill::FILL_NONE
		],
		'alignment' => [
			'vertical' => Alignment::VERTICAL_CENTER
		]
	];

	private static array $title_style = [
		'font' => [
			'bold' => true,
			'color' => ['rgb' => '000000'],
			'size' => 20,
			'name' => 'Arial'
		],
		'fill' => [
			'fillType' => Fill::FILL_NONE
		],
		'alignment' => [
			'vertical' => Alignment::VERTICAL_CENTER
		]
	];

	private static array $header_style = [
		'font' => [
			'bold' => true,
			'color' => ['rgb' => 'FFFFFF'],
			'size' => 12,
			'name' => 'Arial'
		],
		'fill' => [
			'fillType' => Fill::FILL_SOLID,
			'startColor' => ['rgb' => '000000']
		],
		'alignment' => [
			'vertical' => Alignment::VERTICAL_CENTER
		]
	];

	/**
	 * @param bool $include_header_row
	 * @return string[][]
	 */
	public static function GetAllSites(bool $include_header_row): array
	{
		global $wpdb;

		$rows = [];

		if ($include_header_row)
			$rows[] = ['SiteTitle', 'Country', 'URL', 'Copyright', 'Code', 'Backend', 'Entries', 'CreateDate', 'PublishDate', 'ContactEmail', 'LastUpload', 'Notes'];

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

		foreach ($blogs as $blog)  {

			switch_to_blog($blog['blog_id']);

			if (get_theme_mod('show_in_home', 'on') !== 'on')
				continue;

			$blog_details = get_blog_details($blog['blog_id']);
			$fields = [];

			$domain_path = $blog_details->domain . $blog_details->path;

			preg_match_all('~[:en]](.+?)\[~', $blog_details->blogname, $blog_name_matches);
			if(count($blog_name_matches[0]) > 0)
				$fields[] = trim($blog_name_matches[1][0]);
			else
				$fields[] = trim($blog_details->blogname);

			$fields[] = get_option('countryName');

			$fields[] = 'https://' . $domain_path;

			$theme_options = get_option('themezee_options');
			$theme_footer = $theme_options['themeZee_footer'];
			$arr_footer = explode('©', str_replace('®', '', $theme_footer), 2);
			if (count($arr_footer) > 1) {
				$copyright = $arr_footer[1];
				$copyright = preg_replace('/\d{4}/', '', $copyright);
				$copyright = str_replace('[year]', '', $copyright);
				$fields[] = trim($copyright);
			}
			else {
				$fields[] = trim($theme_footer);
			}

			$sql = <<<SQL
SELECT REPLACE(meta_value, 'https://www.ethnologue.com/language/','') AS ethnologue_code
FROM wp_{$blog['blog_id']}_postmeta
WHERE meta_key = '_menu_item_url'
  AND meta_value LIKE '%ethnologue%'
SQL;
			$ethnologue_code = trim($wpdb->get_var($sql));
			$fields[] = $ethnologue_code;

			if (get_option('useCloudBackend')) {
				$fields[] = 'Cloud';

				$dictionary_id = str_replace('/', '', $blog_details->path);
				$dictionary = get_option('dictionary', null) ?? Webonary_Cloud::getDictionaryById($dictionary_id);
				if (is_null($dictionary)) {
					$num_posts = '';
					$last_edit_date = '';
				}
				else {
					$num_posts = $dictionary->mainLanguage->entriesCount;
					$last_edit_date = date("Y-m-d H:m:s", strtotime($dictionary->updatedAt));
				}
			}
			else {
				$fields[] = 'Wordpress';

				$num_posts = $wpdb->get_var("SELECT COUNT(*) FROM wp_{$blog ['blog_id']}_posts WHERE post_status = 'publish' AND post_type = 'post'");
				$last_edit_date = $wpdb->get_var("SELECT post_date FROM wp_{$blog ['blog_id']}_posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date DESC");
			}

			$fields[] = $num_posts;
			$fields[] = $blog['registered'];

			/** @noinspection SqlResolve */
			$published_date = $wpdb->get_var("SELECT DATE_FORMAT(link_updated, '%Y-%m-%d') AS link_updated FROM wp_links WHERE link_url LIKE '%$domain_path%'");
			$fields[]      = $published_date;

			$fields[] = get_option('admin_email');

			if (empty($last_edit_date))
				$fields[] = '';
			elseif ($last_edit_date == '0000-00-00 00:00:00')
				$fields[] = '';
			else
				$fields[] = $last_edit_date;

			$fields[] = stripslashes(get_option('notes'));

			$mapped = array_map(function($a) {

				$s = preg_replace('/<script.+<\/script>/s', '', $a);
				$s = preg_replace('/[\r\n\t]/', ',', $s);
				return htmlspecialchars_decode(str_replace('<sup></sup>', '', $s), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);
			}, $fields);

			$rows[] = $mapped;
		}

		restore_current_blog();

		return $rows;
	}

	public static function GetGrammarSites(bool $include_header_row): array
	{
		global $wpdb;

		$rows = [];

		if ($include_header_row)
			$rows[] = ['DictionaryID', 'LanguageName', 'LanguageFamily', 'Country', 'DictionaryName', 'GrammarLink'];

		$sql = <<<SQL
SELECT b.blog_id, l.link_updated
FROM $wpdb->blogs AS b
  INNER JOIN (
      SELECT table_name
      FROM information_schema.tables
      WHERE table_schema = 'webonary' AND table_name LIKE '%_posts'
) AS t ON table_name LIKE CONCAT('%\_', b.blog_id, '\_posts')
  LEFT JOIN wp_links AS l ON l.link_url LIKE CONCAT('%', b.path)
WHERE NOT b.deleted
  AND b.public
;
SQL;
		$blogs = $wpdb->get_results($sql);

		if (count($blogs) == 0)
			return $rows;

		$settings = WEBONARY_MONGO;
		$mongo = new Client("mongodb+srv://{$settings['usr']}:{$settings['pwd']}@{$settings['url']}");
		$catalog = $settings['cat'];

		/** @noinspection PhpUndefinedFieldInspection */
		$collection = $mongo->$catalog->webonaryDictionaries;

		// get a list of all the sites that have a grammar page
		foreach ($blogs as $blog)  {

			if (empty($blog->link_updated) || $blog->link_updated == '0000-00-00 00:00:00')
				continue;

			$sql = <<<SQL
SELECT COUNT(*)
FROM wp_{$blog->blog_id}_posts AS p
WHERE p.post_name = 'grammar' AND p.post_type = 'page'
SQL;
			$has_grammar = intval($wpdb->get_var($sql) ?? 0);

			if (empty($has_grammar))
				continue;

			/** @var WP_Site $blog_details */
			$blog_details = get_blog_details($blog->blog_id);
			$lower_blog_name = strtolower($blog_details->blogname);

			if (str_starts_with($lower_blog_name, 'test'))
				continue;

			if (str_starts_with($lower_blog_name, 'template'))
				continue;

			switch_to_blog($blog->blog_id);

			$blog_names = array_filter(preg_split('/\[:[^]]*]/m', $blog_details->blogname));
			$blog_name = reset($blog_names);

			if (get_option('useCloudBackend')) {

				$dictionary_id = trim($blog_details->path, '/');

				$language = $collection->findOne(
					['_id' => $dictionary_id],
					['projection' => ['mainLanguage' => 1]]
				);

				$lang_name = $language['mainLanguage']['title'] ?? false;
				$lang_code = $language['mainLanguage']['lang'] ?? false;
			}
			else {
				$lang_code = get_option('languagecode');
				$term = get_term_by('slug', $lang_code, 'sil_writing_systems');
				$lang_name = $term->name ?? false;
			}

			$language_name = $lang_name ?: $lang_code ?: 'Unknown';

			$rows[] = [
				'id' => $blog->blog_id,
				'language' => $language_name,
				'family' => get_option('languageFamily', 'N/A'),
				'country' => get_option('countryName', 'N/A'),
				'blog_name' => $blog_name,
				'url' => path_join($blog_details->path, 'grammar')
			];
		}

		restore_current_blog();

		return $rows;
	}

	/**
	 * @return void
	 * @throws Exception
	 */
	public static function ExportAllSitesToExcel(): void
	{
		set_time_limit(300);
		$sites = self::GetAllSites(true);

		if (count($sites) == 0)
			throw new Exception('No data returned');

		$now = gmdate('Y-m-d\TH.i.s\Z');
		$today = gmdate('Y-m-d');
		$file_name = 'WebonarySites_' . $now;
		$title = 'Webonary Sites: ' . $today;

		self::SendExcel($sites, $title, $file_name);
	}

	/**
	 * @return void
	 * @throws Exception
	 */
	public static function ExportGrammarSitesToExcel(): void
	{
		set_time_limit(300);
		$sites = self::GetGrammarSites(true);

		if (count($sites) == 0)
			throw new Exception('No data returned');

		$now = gmdate('Y-m-d\TH.i.s\Z');
		$today = gmdate('Y-m-d');
		$file_name = 'GrammarSites_' . $now;
		$title = 'Grammar Sites: ' . $today;

		self::SendExcel($sites, $title, $file_name);
	}

	/**
	 * @param array $sites
	 * @param string $title
	 * @param string $file_name
	 * @return void
	 * @throws Exception
	 */
	private static function SendExcel(array $sites, string $title, string $file_name): void
	{
		// get the list of column headers
		$columns = $sites[0];

		// open a new Excel file
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		// set some defaults
		$last_column = Coordinate::stringFromColumnIndex(count($columns));

		// column headers
		for ($i = 0; $i < count($columns); $i++) {
			$sheet->setCellValue([$i + 1, 2], $columns[$i]);
		}

		// data
		$row_idx = 3;
		$column_keys = array_keys($sites[1]);
		for ($j = 1; $j < count($sites); $j++) {
			$data = $sites[$j];

			foreach ($column_keys as $idx => $key) {

				$cell_data = $data[$key];
				$sheet->setCellValue([$idx + 1, $row_idx], $cell_data);

				// check for URL
				if (str_starts_with($cell_data, 'https://'))
					$sheet->getCell([$idx + 1, $row_idx])->getHyperlink()->setUrl($cell_data);
			}

			$row_idx++;
		}

		// default style
		$sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray(self::$regular_style);

		// header style
		$sheet->getStyle('A2:' . $last_column . '2')->applyFromArray(self::$header_style);
		for ($row_idx2 = 2; $row_idx2 <= $row_idx; $row_idx2++) {
			$sheet->getRowDimension($row_idx2)->setRowHeight(18);
		}

		// set column widths
		for ($i = 0; $i < count($columns); $i++) {
			$sheet->getColumnDimensionByColumn($i + 1)->setAutoSize(true);
		}

		$sheet->calculateColumnWidths();

		for ($i = 0; $i < count($columns); $i++) {
			$sheet->getColumnDimensionByColumn($i + 1)->setAutoSize(false);
		}

		$sheet->refreshColumnDimensions();

		for ($i = 0; $i < count($columns); $i++) {
			$dimensions = $sheet->getColumnDimensionByColumn($i + 1);
			$x = $dimensions->getWidth();
			$x = $x * 0.7;
			if ($x < 14)
				$x = 14;

			if ($x > 70)
				$x = 70;

			$dimensions->setWidth($x);
		}

		// report name
		$sheet->mergeCells('A1:' . $last_column . '1');
		$sheet->setCellValue('A1', ucwords($title));

		// title style
		$sheet->getStyle('A1')->applyFromArray(self::$title_style);
		$sheet->getRowDimension(1)->setRowHeight(30);

		// send to the user
		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="' . $file_name . '.xlsx"');
		$writer->save('php://output');
	}
}
