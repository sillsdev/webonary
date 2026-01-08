<?php
// https://github.com/Veraxus/wp-list-table-example/blob/master/includes/class-tt-example-list-table.php
// https://gist.github.com/paulund/7659452

namespace SIL\Webonary;

use WP_List_Table;

class AdminReportTable extends WP_List_Table
{
	public function get_columns(): array
	{
		return [
			'title' => 'Report'
		];
	}

	public function get_hidden_columns(): array
	{
		return [];
	}

	public function get_sortable_columns(): array
	{
		return ['title' => ['title', false]];
	}

	public function prepare_items(): void
	{
		$data = Reports::ReportRoutes();

		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$perPage = 200;
		$currentPage = $this->get_pagenum();
		$totalItems = count($data);

		$this->set_pagination_args(array(
			'total_items' => $totalItems,
			'per_page' => $perPage
		));

		$data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);

		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $data;
	}

	protected function column_default($item, $column_name): string
	{
		return $item[$column_name];
	}

	protected function column_title($item): string
	{
		return <<<HTML
<a href="{$item["href"]}" title="">{$item["title"]}</a>
HTML;
	}

	protected function get_table_classes(): array
	{
		return ['widefat'];
	}
}
