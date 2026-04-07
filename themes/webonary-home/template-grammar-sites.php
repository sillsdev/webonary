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
</style>
<div id="table-container-div" style="width: 100%; box-sizing: border-box; padding: 0 10px">
  <table id="grammar-sites-table" class="stripe" style="width: 100%; box-sizing: border-box">
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

	addEventListener('load', () => {

		let columns = [
			{data: 'language'},
			{data: 'family'},
			{data: 'country'},
			{data: 'region'},
			{data: 'published', type: 'datetime'},
			{data: 'blog_name', render: function(data, _type, row) { return '<a href="' + row['url'] + '" target="_blank">' + data + '</a>'; }},
		];

		DatatablesWebonary.createDataTable(
			'grammar-sites-table',
			'$url',
			columns,
			null,
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
