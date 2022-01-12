<?php

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class Webonary_Excel
{
	private static $regular_style = [
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

	private static $title_style = [
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

	private static $header_style = [
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
	 * @param $file_name
	 * @param $title
	 * @param $rows
	 *
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 * @throws Exception
	 */
	public static function ToExcel($file_name, $title, $rows)
	{
		if (count($rows) == 0)
			throw new Exception('No data returned');

		// get the list of column headers
		//$vars = get_object_vars($rows[0]);
		$columns = $rows[0];

		// open a new Excel file
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		// set some defaults
		$last_column = Coordinate::stringFromColumnIndex(count($columns));

		// column headers
		for ($i = 0; $i < count($columns); $i++) {
			$sheet->setCellValueByColumnAndRow($i + 1, 2, $columns[$i]);
		}

		// data
		$row_idx = 3;
		for ($j = 1; $j < count($rows); $j++) {
			$data = $rows[$j];

			for ($i = 0; $i < count($columns); $i++) {

				$cell_data = $data[$i];
				$sheet->setCellValueByColumnAndRow($i + 1, $row_idx, $cell_data);

				// check for URL
				if (strpos($cell_data, 'https://') === 0)
					$sheet->getCellByColumnAndRow($i + 1, $row_idx)->getHyperlink()->setUrl($cell_data);
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

	/**
	 * @param $is_excel
	 *
	 * @return void
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 */
	public static function DisplayAllSites($is_excel)
	{
		set_time_limit(300);

		$sites = self::GetAllSites();

		$is_excel = strtolower($atts['excel'] ?? '');
		if ($is_excel == 'yes' || $is_excel == 'true' || $is_excel == '1')
			self::ExcelRows($sites);
		else
			self::EchoRows($sites);
	}

	/**
	 * @return string[][]
	 */
	private static function GetAllSites(): array
	{
		global $wpdb;

		$rows = [['Site Title', 'Country', 'URL', 'Copyright', 'Code', 'Entries', 'CreateDate', 'PublishDate', 'ContactEmail', 'Notes', 'LastImport']];

		$sql =  "SELECT blog_id, domain, DATE_FORMAT(registered, '%Y-%m-%d') AS registered FROM $wpdb->blogs
    WHERE blog_id != $wpdb->blogid
    AND site_id = '$wpdb->siteid'
    AND spam = '0'
    AND deleted = '0'
    AND archived = '0'
    order by registered DESC";

		$blogs = $wpdb->get_results($sql, ARRAY_A);

		if (count($blogs) == 0)
			return $rows;

		foreach ($blogs as $blog)  {

			switch_to_blog($blog[ 'blog_id' ]);

			if (get_theme_mod('show_in_home', 'on') !== 'on')
				continue;

			$blog_details = get_blog_details( $blog[ 'blog_id' ] );
			$fields = [];

			$domainPath = $blog_details->domain . $blog_details->path;

			preg_match_all('~[:en]](.+?)[\\[]~', $blog_details->blogname, $blognameMatches);
			if(count($blognameMatches[0]) > 0)
				$fields[] = $blognameMatches[1][0];
			else
				$fields[] = $blog_details->blogname;

			$fields[] = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'countryName'");

			$fields[] = 'https://' . $domainPath;

			$themeOptions = get_option('themezee_options');
			$theme_footer = $themeOptions['themeZee_footer'];
			$arrFooter = explode('©', str_replace('®', '', $theme_footer), 2);
			if (count($arrFooter) > 1) {
				$copyright = $arrFooter[1];
				$copyright = preg_replace('/\d{4}/', '', $copyright);
				$copyright = str_replace('[year]', '', $copyright);
				$fields[] = trim($copyright);
			}
			else {
				$fields[] = trim($theme_footer);
			}

			$sql = "SELECT REPLACE(meta_value, 'https://www.ethnologue.com/language/','') AS ethnologueCode " . " FROM wp_" . $blog ['blog_id'] . "_postmeta " . " WHERE meta_key = '_menu_item_url' AND meta_value LIKE '%ethnologue%'";

			$ethnologue_code = trim($wpdb->get_var ( $sql ));
			$fields[] = $ethnologue_code;

			$numpost = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post'");
			$fields[] = $numpost;
			$fields[] = $blog['registered'];

			/** @noinspection SqlResolve */
			$publishedDate = $wpdb->get_var( "SELECT DATE_FORMAT(link_updated, '%Y-%m-%d') AS link_updated FROM wp_links WHERE link_url LIKE '%" . $domainPath . "%'");
			$fields[]      = $publishedDate;

			$email = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'admin_email'");

			$fields[] = $email;

			$notes = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'notes'");

			$fields[] = stripslashes($notes);

			$lastEditDate = $wpdb->get_var("SELECT post_date FROM wp_" . $blog ['blog_id'] . "_posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date DESC");
			if (empty($lastEditDate))
				$fields[] = '';
			elseif ($lastEditDate == '0000-00-00 00:00:00')
				$fields[] = '';
			else
				$fields[] = $lastEditDate;

			$mapped = array_map(function($a) {

				$s = preg_replace('/<script.+<\/script>/s', '', $a);
				$s = preg_replace('/[\r\n\t]/', ',', $s);
				return htmlspecialchars_decode(str_replace('<sup></sup>', '', $s), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);
			}, $fields);

			$rows[] = $mapped;

			restore_current_blog();
		}

		return $rows;
///** @noinspection PhpUnhandledExceptionInspection */
//Webonary_Excel::ToExcel( 'WebonarySites_' . $now, 'Webonary Sites: ' . $today, $rows);
	}

	private static function EchoRows($rows)
	{
		$row_count = count($rows);

		if ($row_count == 0) {
			echo '<p>No results found.</p>';
			return;
		}

		echo '<div style="overflow-x: auto"><table style="white-space: nowrap">';

		// the header row
		$row = $rows[0];
		$cells = '<th>&nbsp;</th>';
		foreach($row as $col) {
			$cells .= "<td>$col</td>";
		}

		echo <<<HTML
<thead>
<tr>
  <th>$cells</th>
</tr>
</thead>
<tbody>
HTML;

		// the data rows
		for ($i=1; $i < $row_count; $i++) {

			$cells = "<td>$i</td>";

			foreach($rows[$i] as $col) {

				if (strpos($col, 'http') === 0)
					$col = "<a href='$col' target='_blank'>$col</a>";

				$cells .= "<td>$col</td>";
			}

			echo <<<HTML
<tr>
  <th>$cells</th>
</tr>
HTML;
		}

		echo '</tbody></table></div>';
	}

	/**
	 * @param $rows
	 *
	 * @return void
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 */
	private static function ExcelRows($rows)
	{
		$now = gmdate('Y-m-d\TH.i.s\Z');
		$today = gmdate('Y-m-d');
		Webonary_Excel::ToExcel( 'WebonarySites_' . $now, 'Webonary Sites: ' . $today, $rows);
	}
}
