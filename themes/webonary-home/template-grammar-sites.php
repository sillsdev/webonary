<?php
/*
Template Name: Display Grammar Sites
*/

include_once 'includes/src/WebonaryHome_Ajax.php';

/** @noinspection JSUnresolvedReference */
function BuildTable(): void
{
	$url = admin_url('admin-ajax.php');
	if (!str_contains($url, '?'))
		$url .= '?action=getAjaxGrammarSites';
	else
		$url .= '&action=getAjaxGrammarSites';

	echo <<<HTML
<style>
  #grammar-sites-table tbody td {font-weight: 400; font-size: 13px; vertical-align: top}
  #grammar-sites-table span {border-bottom: 1px dashed #000}
  div.dt-buttons {display: none}
  #grammar-sites-table tbody tr:nth-child(odd) {background-color: #ebebef;}
</style>
<div id="table-container-div" style="width: 100%; box-sizing: border-box; padding: 0 10px">
  <table id="grammar-sites-table" style="width: 100%; box-sizing: border-box">
    <thead>
      <tr>
        <th>Language</th>
        <th>Family</th>
        <th>Country</th>
        <th>Region</th>
        <th>Published</th>
        <th>Dictionary</th>
	</tr>
    </thead>
    <tbody></tbody>
  </table>
</div>
<script type="text/javascript">

    function setTableHeight() {

        let container = $('#grammar-sites-table').closest('.dataTables_scroll');
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
        let table = $('#grammar-sites-table');

	    table.DataTable({
	        ajax: '$url',
	        paging: false,
	        sScrollY: 'auto',
            scrollY: false,
            sScrollX: '100%',
            scrollX: true,
	        ordering: true,
	        order: [[0, 'asc']],
	        columns: [
				{data: 'language'},
				{data: 'family'},
				{data: 'country'},
				{data: 'region'},
				{data: 'published'},
				{data: 'blog_name', render: function(data, _type, row) { return '<a href="' + row['url'] + '" target="_blank">' + data + '</a>'; }},
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
	WebonaryHome_Ajax::ExportGrammarSitesToExcel();
	exit();
}

get_header();

$id = $id = get_the_ID();
$post = get_post($id);
$post_class = esc_attr(implode(' ', get_post_class('', $post)));
$post_title = '';
$post_content = '';

if (!empty($post->post_title))
	$post_title = "<h2 style='margin-bottom: 1rem'>$post->post_title</h2>";

if (!empty($post->post_content))
	$post_content = <<<HTML
<div class="entry">
  $post->post_content
  <div class="clear"></div>
</div>
HTML;

if (!empty($post_title) || !empty($post_content))
	echo <<<HTML
<div id="content">
  <div id="page-$id" class="$post_class">
    $post_title
    $post_content
  </div>
</div>
HTML;

BuildTable();

get_footer();
