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
		if ($is_excel) {
			set_time_limit(300);
			$sites = self::GetAllSites();
			self::ExcelRows($sites);
		}
		else {
			self::BuildTable();
		}
	}

	/**
	 * @return string[][]
	 */
	public static function GetAllSites($include_header_row = true): array
	{
		global $wpdb;

		$rows = [];

		if ($include_header_row)
			$rows[] = ['SiteTitle', 'Country', 'URL', 'Copyright', 'Code', 'Backend', 'Entries', 'CreateDate', 'PublishDate', 'ContactEmail', 'LastUpload', 'Notes'];

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
				$fields[] = trim($blognameMatches[1][0]);
			else
				$fields[] = trim($blog_details->blogname);

			$fields[] = get_option('countryName');

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

			if (get_option('useCloudBackend')) {
				$fields[] = 'Cloud';

				$dictionary = Webonary_Cloud::getDictionary();
				if (is_null($dictionary)) {
					$numpost = '';
					$lastEditDate = '';
				}
				else {
					$numpost = $dictionary->mainLanguage->entriesCount;
                    $lastEditDate = date("Y-m-d H:m:s", strtotime($dictionary->updatedAt));
				}
			}
			else {
				$fields[] = 'Wordpress';

				$numpost = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post'");
				$lastEditDate = $wpdb->get_var("SELECT post_date FROM wp_" . $blog ['blog_id'] . "_posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date DESC");
			}

			$fields[] = $numpost;
			$fields[] = $blog['registered'];

			/** @noinspection SqlResolve */
			$publishedDate = $wpdb->get_var( "SELECT DATE_FORMAT(link_updated, '%Y-%m-%d') AS link_updated FROM wp_links WHERE link_url LIKE '%" . $domainPath . "%'");
			$fields[]      = $publishedDate;

			$fields[] = get_option('admin_email');

			if (empty($lastEditDate))
				$fields[] = '';
			elseif ($lastEditDate == '0000-00-00 00:00:00')
				$fields[] = '';
			else
				$fields[] = $lastEditDate;

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


	private static function BuildTable()
	{
		$url = admin_url('admin-ajax.php');
		if (strpos($url, '?') === false)
			$url .= '?action=getAjaxDisplaySites';
		else
			$url .= '&action=getAjaxDisplaySites';

		echo <<<HTML
<style>
  #all-sites-table tbody td {font-weight: 400; font-size: 13px; vertical-align: top}
  #all-sites-table span {border-bottom: 1px dashed #000}
  div.dt-buttons {display: none}
</style>
<div id="table-container-div" style="width: 100%; box-sizing: border-box; padding: 0 10px">
  <table id="all-sites-table" style="width: 100%; box-sizing: border-box">
    <thead>
      <tr>
        <th>Site Title</th>
        <th>Country</th>
        <th>URL</th>
        <th>Copyright</th>
        <th>Code</th>
        <th>Backend</th>
        <th>Entries</th>
        <th>Create Date</th>
        <th>Publish Date</th>
        <th>Contact Email</th>
        <th>Last Upload</th>
        <th>Notes</th>
	</tr>
    </thead>
    <tbody></tbody>
  </table>
</div>
<script type="text/javascript">

    function fixedRender(data, len) {
        if (data.length <= len)
            return data;

        return '<span title="' + data + '">' + data.substring(0, len) + '</span>';
    }

    function dateTimeRender(data) {
    	return '<span style="white-space: nowrap">' + data + '</span>';
    }

    function setTableHeight() {

        let container = $('#all-sites-table').closest('.dataTables_scroll');
        let tbody = container.find('.dataTables_scrollBody');
        let offset = tbody.offset().top + 1;

        let card = tbody.closest('#table-container-div');
        let padding = parseInt(card.css('padding-bottom'));
        if (padding)
            offset += padding;

        let paginate = $('#list-table_paginate');
        if (paginate.length)
            offset += paginate.outerHeight(true) + 2;

        let filter = $('div.list-table-filter');
        if (filter.length) {

            if (!paginate.length) {
                offset += filter.outerHeight(true) + 2;
            }
        }

        let footer = container.find('.dataTables_scrollFootInner');
        if (footer.length)
            offset += footer.outerHeight(true) + 2;

        tbody.css('max-height', 'calc(100vh - ' + (offset + 30).toString() + 'px)');
    }

	$(document).ready(function() {
        let table = $('#all-sites-table');

	    table.DataTable({
	        ajax: '$url',
	        paging: false,
	        sScrollY: 'auto',
            scrollY: false,
            sScrollX: '100%',
            scrollX: true,
	        ordering: true,
	        order: [[0, 'asc']],
	        columnDefs: [
                {
                    targets: 2,
                    render: function(data) { return '<a href="' + data + '" target="_blank">' + data + '</a>'; }
                },
                {
                    targets: 4,
                    render: function(data) { return fixedRender(data, 6); }
                }
                ,
                {
                    targets: [6, 7, 10],
                    render: function(data) { return dateTimeRender(data); }
                }
	        ],
	        initComplete: function() {
                setTableHeight();
	        }
	    });

        let tbody = table.find('tbody');

        tbody.on('click', 'tr', function() {

	        let tr = $(this).closest('tr');
	        let tbl = $(this).closest('table').DataTable();
	        tbl.row(tr).select();
	        tbl.rows(tr.siblings()).deselect();
        });
	});
</script>
HTML;
	}
}
