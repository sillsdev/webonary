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
}
