<?php

namespace SIL\Webonary\Abstracts;

use JetBrains\PhpStorm\NoReturn;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use ReflectionClass;
use SIL\Webonary\Attributes\Report;
use SIL\Webonary\Helpers\Request;

trait AdminReportTrait
{
	protected static ?array $attributes = null;

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

	protected bool $is_excel;

	public function Run(): string
	{
		$this->is_excel = Request::GetInt('excel') === 1;

		if ($this->is_excel)
			$this->GetExcel();

		return $this->GetReport();
	}

	abstract protected function GetReportData(): array;

	protected static function GetAttributes(): array
	{
		if (!isset(static::$attributes)) {
			$rc = new ReflectionClass(static::class);
			static::$attributes = $rc->getAttributes(Report::class)[0]->getArguments();
		}

		return static::$attributes;
	}

	public static function GetReportTitle(): string
	{
		return static::GetAttributes()['title'];
	}

	public static function GetReportSlug(): string
	{
		return static::GetAttributes()['slug'];
	}

	public static function GetShowInList(): string
	{
		return static::GetAttributes()['show_in_list'];
	}

	protected function get_table_classes(): array
	{
		return ['widefat', 'striped'];
	}

	protected function column_default($item, $column_name): string
	{
		return $item[$column_name];
	}

	public function get_hidden_columns(): array
	{
		return [];
	}

	protected function GetExcelButton(): string
	{
		return <<<'HTML'
<button type="button" class="button action" style="display:flex; align-items:center; column-gap:1ex" onclick="WebonaryAdmin.ExportReport();">
    <svg viewBox="0 0 384 512" style="height:1.5em; fill:var(--wp-admin-theme-color-darker-20)"><use xlink:href="#fa-excel"></use></svg>
	<span>Export to Excel</span>
</button>
HTML;
	}

	protected function GetReportTable(): string
	{
		$this->prepare_items();

		ob_start();
		$this->display();
		return ob_get_clean();
	}

	protected function GetReport(string $title = null): string
	{
		if (empty($title))
			$title = $this->GetReportTitle();

		// opening tags
		$lines = [
			'<div class="wrap">',
			'<h1>' . $title . '</h1>',
			'<style>.wp-list-table {th:nth-child(1), td:nth-child(1) {width: 20px; white-space: nowrap}}</style>'
		];

		$lines[] = $this->GetReportTable();

		// closing tags
		$lines[] = '</div>';

		$return_val = implode(PHP_EOL, $lines);

		if (!defined('PHP_UNIT'))
			echo $return_val;

		return $return_val;
	}

	#[NoReturn] protected function GetExcel(string $title = null): void
	{
		if (empty($title))
			$title = $this->GetReportTitle();

		$data = $this->GetReportData();
		$now = gmdate('Y-m-d\TH.i.s\Z');
		$today = gmdate('Y-m-d');
		$file_name = str_replace(' ', '_', $title . '_' . $now);
		$title = $title . ': ' . $today;

		$columns = $this->get_columns();

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		// set some defaults
		$last_column = Coordinate::stringFromColumnIndex(count($columns));

		// column headers
		$col_idx = 0;
		foreach ($columns as $col_header) {
			$col_idx++;
			$sheet->setCellValue([$col_idx, 2], $col_header);
		}

		// data
		$row_idx = 2;
		foreach ($data as $record) {

			$col_idx = 0;
			$row_idx++;

			foreach (array_keys($columns) AS $key) {

				$col_idx++;
				$cell_data = $record[$key];
				$sheet->setCellValue([$col_idx, $row_idx], $cell_data);

				// check for URL
				if (str_starts_with($cell_data, 'https://'))
					$sheet->getCell([$col_idx, $row_idx])->getHyperlink()->setUrl($cell_data);
			}
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
//		$sheet->mergeCells('A1:' . $last_column . '1');
		$sheet->setCellValue('A1', $title);

		// title style
		$sheet->getStyle('A1')->applyFromArray(self::$title_style);
		$sheet->getRowDimension(1)->setRowHeight(30);

		// send to the user
		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="' . $file_name . '.xlsx"');
		$writer->save('php://output');

		exit();
	}

	public function extra_tablenav($which): void
	{
		if ($which != 'top')
			return;

		$lines = [
			'<div class="alignright actions">',
			$this->GetExcelButton(),
			'</div>'
		];

		echo implode(PHP_EOL, $lines);
	}

	/**
	 * Displays a date or date/time with non-breaking characters.
	 *
	 * @param $value
	 * @return string
	 */
	protected function NonBreakingDate($value): string
	{
		if ($this->is_excel)
			return $value ?? '';

		return str_replace(' ', '&nbsp;', str_replace('-', '&#8209;', $value ?? ''));
	}
}
