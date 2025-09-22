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
  #all-sites-table span {border-bottom: 1px dashed #000}
  div.dt-buttons {display: none}
</style>
<div id="table-container-div" style="width: 100%; box-sizing: border-box; padding: 0 10px">
  <table id="all-sites-table" style="width: 100%; box-sizing: border-box">
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
                    targets: 3,
                    render: function(data) { return '<a href="' + data + '" target="_blank">' + data + '</a>'; }
                },
                {
                    targets: 5,
                    render: function(data) { return fixedRender(data, 6); }
                }
                ,
                {
                    targets: [7, 8, 11],
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
