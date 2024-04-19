<?php /** @noinspection SqlResolve */
/** @noinspection HtmlFormInputWithoutLabel */
/** @noinspection HtmlUnknownTarget */

add_action('wp_ajax_getAjaxLanguage', 'Webonary_Ajax::ajaxLanguage');
add_action('wp_ajax_nopriv_getAjaxLanguage', 'Webonary_Ajax::ajaxLanguage');
add_action('wp_ajax_getAjaxCurrentIndexedCount', 'Webonary_Ajax::ajaxCurrentIndexedCount');
add_action('wp_ajax_getAjaxCurrentImportedCount', 'Webonary_Ajax::ajaxCurrentImportedCount');
add_action('wp_ajax_getAjaxRestartIndexing', 'Webonary_Ajax::ajaxRestartIndexing');
add_action('wp_ajax_getAjaxDisplaySites', 'Webonary_Ajax::ajaxDisplaySites');
add_action('wp_ajax_postAjaxDeleteData', 'Webonary_Ajax::deleteData');

function relevanceSave(): bool
{
	global $wpdb;

	$tableCustomRelevance = $wpdb->prefix . 'custom_relevance';

	$class_names = Webonary_Filters::PostArray('classname');
	$relevances = Webonary_Filters::PostArray('relevance');

	for ($i = 0; $i < count($class_names); $i++) {

		$relevance = intval($relevances[$i]);
		if ($relevance < 0 || $relevance > 99) {
			echo '<span style="color: red;">Relevance has to be >= 0 and < 100 for all fields!</span><br>';
			return false;
		}

		$class_name = $class_names[$i];

		$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}sil_search SET relevance = %s WHERE class = %s", $relevance, $class_name));

		$found = Webonary_Db::GetBool("SELECT COUNT(*) FROM $tableCustomRelevance WHERE class = %s", $class_name);

		if ($found) {
			$wpdb->query($wpdb->prepare("UPDATE {$tableCustomRelevance} SET relevance = %s WHERE class = %s", $relevance, $class_name));
		}
		else {
			$wpdb->query($wpdb->prepare("INSERT INTO {$tableCustomRelevance} (relevance, class) VALUES (%s, %s)", $relevance, $class_name));
		}
	}

	$r = 0;
	foreach($_POST['classname'] as $class)
	{
		if($_POST['relevance'][$r] < 0 || $_POST['relevance'][$r] > 99 || !is_numeric($_POST['relevance'][$r]))
		{
			echo '<span style="color: red;">Relevance has to be >= 0 and < 100 for all fields!</span><br>';
			return false;
		}
		//echo $class . ": " . $_POST['relevance'][$r] . "<br>";

		$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}sil_search SET relevance = %s WHERE class = %s", $_POST['relevance'][$r], $class));

		$result = $wpdb->get_results($wpdb->prepare('SELECT relevance FROM $tableCustomRelevance WHERE class = %s', $class));

		if (count ($result) > 0) {
			$wpdb->query($wpdb->prepare("UPDATE {$tableCustomRelevance} SET relevance = %s WHERE class = %s", $_POST['relevance'][$r], $class));
		}
		else {
			$wpdb->query($wpdb->prepare("INSERT INTO {$tableCustomRelevance} (class, relevance) VALUES (%s, %s)", $class, $_POST['relevance'][$r]));
		}

		$r++;
	}

	$wpdb->print_error();

	if($wpdb->last_error === '')
	{
		echo "<h3>Relevance Settings were saved.</h3>";
	}
	echo "<hr>";

	return true;
}

//display the senses that don't get linked in the reversal browse view
function report_missing_senses(): void
{
	global $wpdb;

	$sql = <<<SQL
SELECT search_strings
FROM {$wpdb->prefix}sil_search
WHERE post_id = 0 AND language_code = '{$_GET['languageCode']}'
SQL;

	$arrMissing = $wpdb->get_results($sql);
	$missing_items = '';
	foreach($arrMissing as $missing) {
		$missing_items .= "<li>{$missing->search_strings}</li>\n";
	}

	$html = <<<HTML
	<div class="wrap">
		<h2>Missing Senses for the {$_GET['language']} browse view</h2>
		One or more senses will not get found for the following entries when clicking on them in the browse view.<br>
		Please check in the FLEx dictionary view, if they show up there.
		<ul>
			{$missing_items}
		</ul>
		<a href="admin.php?page=webonary">Back to the Webonary settings</a>
	</div>
HTML;

	echo $html;
}

/**
 * @return void
 * @throws Exception
 */
function webonary_conf_dashboard(): void
{
	webonary_conf_widget(true);
}

function webonary_register_custom_css(): void
{
	$upload_dir = wp_upload_dir();
	wp_register_style(
		'custom_stylesheet',
		$upload_dir['baseurl'] . '/custom.css',
		[],
		date('U'),
		'all'
	);
	wp_enqueue_style('custom_stylesheet');
}
add_action('wp_enqueue_scripts', 'webonary_register_custom_css', 999993);

/**
 * @param bool $showTitle
 * @return void
 * @throws Exception
 */
function webonary_conf_widget(bool $showTitle = false): void
{
	Webonary_Configuration_Widget::UpdateConfiguration();
	Webonary_Configuration_Widget::DisplayConfiguration();
}
