<?php
/*
Template Name: Display Sites
*/

include_once 'includes/src/WebonaryHome_Ajax.php';

/** @noinspection JSUnresolvedReference */
function BuildTable(): void
{
	$url = admin_url('admin-ajax.php');
	if (!str_contains($url, '?'))
		$url .= '?action=getAjaxDisplaySites';
	else
		$url .= '&action=getAjaxDisplaySites';

	echo <<<HTML
<style>
  #all-sites-table tbody td {font-weight: 400; font-size: 13px; vertical-align: top}
</style>
<div id="table-container-div" style="width: 100%; box-sizing: border-box; padding: 0 10px">
  <table id="all-sites-table" class="stripe" style="width: 100%; box-sizing: border-box">
    <thead>
      <tr>
        <th>Site Title</th>
        <th>Country</th>
        <th>Region</th>
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


    function dateTimeRender(data) {
    	return '<span style="white-space: nowrap">' + data + '</span>';
    }

	addEventListener('load', () => {

		let column_defs = [
			{
				targets: 3,
				render: function(data) { return '<a href="' + data + '" target="_blank">' + data + '</a>'; }
			},
			{
				targets: 7,
				type: 'number'
			},
			{
				targets: [8, 9, 11],
				type: 'datetime',
				render: function(data) { return dateTimeRender(data); }
			}
		];

		DatatablesWebonary.createDataTable(
			'all-sites-table',
			'$url',
			null,
			column_defs,
			[[0, 'asc']],
			$('<button type="button" onclick="window.open(\'?excel\', \'_blank\');" class="spbutton">Excel</button>')
		);
	});
</script>
HTML;
}

add_filter('body_class','full_width_body_classes');

$url = strtolower($_SERVER['REQUEST_URI']);
$is_excel = str_contains($url, 'excel');

if ($is_excel) {
	/** @noinspection PhpUnhandledExceptionInspection */
	WebonaryHome_Ajax::ExportAllSitesToExcel();
	exit();
}

get_header();

BuildTable();

get_footer();
