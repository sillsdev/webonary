<?php


class Webonary_Configuration
{

	//region Table and taxonomy attributes
	public static $search_table_name = SEARCHTABLE;
	public static $reversal_table_name = REVERSALTABLE;
	public static $pos_taxonomy = 'sil_parts_of_speech';
	public static $semantic_domains_taxonomy = 'sil_semantic_domains';
	//endregion

	/**
	 * Set up the SIL Dictionary in WordPress Dashboard Tools
	 */
	public static function add_admin_menu()
	{
		$data = get_userdata( get_current_user_id() );
		$role = ( array ) $data->roles;

		if ( $role[0] == "editor" || $role[0] == "administrator" || is_super_admin())
		{
			add_menu_page( "Webonary", "Webonary", 'edit_pages', "webonary", "webonary_conf_dashboard",  get_bloginfo('wpurl') . "/wp-content/plugins/sil-dictionary-webonary/images/webonary-icon.png", 76 );
			add_submenu_page('edit.php', 'Missing Senses', 'Missing Senses', 3, __FILE__, 'report_missing_senses');
			remove_submenu_page('edit.php', 'sil-dictionary-webonary/include/configuration.php');
		}
	}

	public static function on_admin_bar()
	{
		/** @var WP_Admin_Bar $wp_admin_bar */
		global $wp_admin_bar;

		$wp_admin_bar->add_menu(array(
			'id' => 'Webonary',
			'title' => 'Webonary',
			'parent' => 'site-name',
			'href' => admin_url('/admin.php?page=webonary'),
		));

		$wp_admin_bar->remove_menu( "themes" );
		$wp_admin_bar->remove_menu( "widgets" );
		$wp_admin_bar->remove_menu( "menus" );
	}

	public static function get_admin_sections()
	{
		$admin_sections = array();

		$admin_sections['import'] = __('Data (Import)', 'sil_dictionary');
		$admin_sections['search'] = __('Search', 'sil_dictionary');
		$admin_sections['browse'] = __('Browse Views', 'sil_dictionary');
		$admin_sections['fonts'] = __('Fonts', 'sil_dictionary');
		if(is_super_admin())
			$admin_sections['superadmin'] = __('Super Admin', 'sil_dictionary');

		return $admin_sections;
	}

	public static function admin_section_start($nm)
	{
		echo '<div id="tab-' . $nm . '" class="hidden">' . PHP_EOL;
	}

	public static function admin_section_end($nm, $button_name=null, $button_class='button-primary')
	{
		if(!empty($button_name))
			echo '<p class="submit" style="float:left;"><input type="submit" name="save_settings" class="'.$button_class.'" value="'.$button_name.'" /></p><br><br>';

		echo '</div>'.PHP_EOL; //'<!-- id="tab-'.$nm.'" -->';
	}

	public static function get_LanguageCodes($language_code = null)
	{
		global $wpdb;

		if (is_null($language_code))
			$language_code = '';
		else
			$language_code = trim($language_code);

		/** @noinspection SqlResolve */
		$sql = <<<SQL
SELECT s.language_code, MAX(t.`name`) AS `name`
FROM {$wpdb->prefix}sil_search AS s
LEFT JOIN {$wpdb->terms} AS t ON t.slug = s.language_code
WHERE IF(%s = '', s.language_code, %s) = s.language_code
GROUP BY s.language_code
ORDER BY s.language_code
SQL;
		$sql = $wpdb->prepare($sql, array($language_code, $language_code));

		return $wpdb->get_results($sql, 'ARRAY_A');
	}

	private static function SkipClass($class)
	{
		return (
			strpos($class->classname, 'abbr') !== false
			|| strpos($class->classname, 'partofspeech') !== false
			|| strpos($class->classname, 'name') !== false
			|| (strpos($class->classname, 'headword') !== false && $class->relevance == 0)
		);
	}

	private static function FixedRelevance($class)
	{
		return (
			$class->relevance == 100 && (strpos($class->classname, 'headword') !== false
				                         || strpos($class->classname, 'lexemeform') !== false
				                         || strpos($class->classname, 'reversalform') !== false)
		);
	}

	public static function relevanceForm()
	{
		global $wpdb;

		/** @noinspection SqlResolve */
		$sql = <<<SQL
SELECT `class` AS classname, MAX(`relevance`) AS `relevance`
FROM {$wpdb->prefix}sil_search
GROUP BY `class`
ORDER BY relevance DESC
SQL;

		$arrClasses = $wpdb->get_results($sql);

		if (empty($arrClasses) || empty($arrClasses[0]->classname)) {
			$part = '<strong>You need to reimport this dictionary if you want to change the relevance settings.</strong>';
		}
		else {

			$list_items = array();

			foreach($arrClasses as $class) {

				if (self::SkipClass($class))
				    continue;

				$li = "<div><strong>{$class->classname}: </strong></div>";

				if (self::FixedRelevance($class))
				{
					$li .= $class->relevance;
				}
				else {
					$li .= <<<HTML
<div>
    <input type="hidden" name=classname[] value="{$class->classname}">
    <input type="text" name=relevance[] size=5 value="{$class->relevance}">
</div>
HTML;
				}

				$list_items[] = "<li>{$li}</li>";
			}

			$part = '<ul>' . implode(PHP_EOL, $list_items) . '</ul>';
			$part .= '<p><input type="submit" name="btnSaveRelevance" value="Save"></p>';
		}

		/** @noinspection HtmlUnknownTarget */
		return <<<HTML
<form action="admin.php?page=webonary#search" method="post" enctype="multipart/form-data">
    <h1>Relevance Settings for Fields</h1>
    <p>The search returns results based on relevance. That is, if the word you are looking for is found in a headword, that will be more important than finding the word in a definition for another word.</p>
    <p>Normally you don't need to change anything here. But if you import a custom field, it will be imported with a relevance of zero in which case you have the option to change the relevance setting.</p>
    {$part}
</form>
HTML;
	}
}
