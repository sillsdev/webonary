<?php /** @noinspection SqlResolve */
/**
 * Infrastructure
 *
 * Infrastructure for SIL Dictionaries. Includes custom tables and custom taxonomies.
 *
 * PHP version 5.2
 *
 * LICENSE GPL v2
 *
 * @package WordPress
 * @since 3.1
 */

// don't load directly
if ( ! defined('ABSPATH') )
	die( '-1' );


/**
 * Install the SIL dictionary infrastructure if needed.
 */
function install_sil_dictionary_infrastructure()
{
	if(is_admin())
	{
		create_custom_relevance();
		create_search_tables();
		create_reversal_tables();
		set_options();
		remove_double_terms();
	}
	set_field_sortorder();
	//upload_stylesheet();
	register_semantic_domains_taxonomy();
	register_part_of_speech_taxonomy();
	register_language_taxonomy();
	register_webstrings_taxonomy();
}

function create_custom_relevance()
{
	global $wpdb;

	$tableCustomRelevance = $wpdb->prefix . "custom_relevance";
	$char_set = MYSQL_CHARSET;
	$collate = MYSQL_COLLATION;

	$sql = <<<SQL
CREATE TABLE IF NOT EXISTS {$tableCustomRelevance} (
  class VARCHAR(50) NOT NULL PRIMARY KEY,
  relevance TINYINT
) DEFAULT CHARSET={$char_set} COLLATE={$collate};
SQL;

	include_once ABSPATH . 'wp-admin/includes/upgrade.php';

	dbDelta( $sql );
}

function create_reversal_tables ()
{
	$table = REVERSALTABLE;
	$char_set = MYSQL_CHARSET;
	$collate = MYSQL_COLLATION;

	$sql = <<<SQL
CREATE TABLE {$table} (
  id VARCHAR(50) NOT NULL,
  language_code VARCHAR(30),
  reversal_head LONGTEXT,
  reversal_content LONGTEXT,
  sortorder INT NOT NULL DEFAULT '0',
  browseletter VARCHAR(5),
  UNIQUE KEY idx_{$table}_id (id)
) DEFAULT CHARSET={$char_set} COLLATE={$collate};
SQL;

	include_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

function create_search_tables ()
{
	global $wpdb;

	$table = SEARCHTABLE;
	$char_set = MYSQL_CHARSET;
	$collate = MYSQL_COLLATION;

	// if the table doesn't exist, create it now
	$sql = "SHOW TABLES LIKE '{$table}';";
	$results = $wpdb->get_results($sql);
	if (empty($results)) {

		$sql = <<<SQL
CREATE TABLE {$table} (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
  post_id bigint(20) NOT NULL,
  language_code varchar(30),
  relevance tinyint,
  search_strings longtext,
  class varchar(50),
  subid INT NOT NULL DEFAULT  '0',
  sortorder INT NOT NULL DEFAULT '0',
  UNIQUE INDEX idx_unique_row (post_id, language_code, relevance, search_strings (150), class (50)),
  INDEX relevance_idx (relevance)
) DEFAULT CHARSET={$char_set} COLLATE={$collate};
SQL;
		$wpdb->query($sql);
		return;
	}


	// remove the old primary key with multiple fields
	$sql = "SHOW KEYS FROM {$table} WHERE Key_name = 'PRIMARY';";
	$results = $wpdb->get_results($sql);
	if (!empty($results) && count($results) > 1) {
		$sql = "ALTER TABLE {$table} DROP PRIMARY KEY";
		$wpdb->query($sql);
	}

	// make sure the `class` field exists
	$sql = "SHOW COLUMNS FROM {$table} LIKE 'class';";
	$results = $wpdb->get_results($sql);
	if (empty($results)) {

		$sql = "ALTER TABLE {$table} ADD `class` VARCHAR(50) NULL;";
		$wpdb->query($sql);
	}

	// add a unique constraint to replace the removed primary key
	$sql = "SHOW KEYS FROM {$table} WHERE Key_name = 'idx_unique_row';";
	$results = $wpdb->get_results($sql);
	if (empty($results)) {
		/** @noinspection SqlResolve */
		$sql = "CREATE UNIQUE INDEX idx_unique_row ON {$table} (post_id, language_code, relevance, search_strings (150), class);";
		$wpdb->query($sql);
	}

	// add a new auto-increment field for the new primary key
	$sql = "SHOW FIELDS FROM {$table} WHERE Field = 'id';";
	$results = $wpdb->get_results($sql);
	if (empty($results)) {

		// just in case there is an existing primary key
		$sql = "SHOW KEYS FROM {$table} WHERE Key_name = 'PRIMARY';";
		$results = $wpdb->get_results($sql);
		if (!empty($results) && count($results) > 1) {
			$sql = "ALTER TABLE {$table} DROP PRIMARY KEY";
			$wpdb->query($sql);
		}

		// add the new primary key field
		$sql = "ALTER TABLE {$table} ADD id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY FIRST;";
		$wpdb->query($sql);
	}
}

/**
 * Provide a taxonomy for semantic domains in an online dictionary.
 */
function register_semantic_domains_taxonomy ()
{
	$labels = array(
		'name' => _x( 'Semantic Domains', 'taxonomy general name' ),
		'singular_name' => _x( 'Semantic Domain', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search Domains' ),
		'all_items' => __( 'All Semantic Domains' ),
		'parent_item' => __( 'Parent Semantic Domain' ),
		'parent_item_colon' => __( 'Parent Semantic Domain:' ),
		'edit_item' => __( 'Edit Semantic Domain' ),
		'update_item' => __( 'Update Semantic Domain' ),
		'add_new_item' => __( 'Add New Semantic Domain' ),
		'new_item_name' => __( 'New Semantic Domain Name' ),
		'menu_name' => __( 'Semantic Domain' ),
	);

	register_taxonomy(
		'sil_semantic_domains',
		'post',
		array(
			'hierarchical' => false,
			'labels' => $labels,
			'update_count_callback' => '_update_post_term_count',
			'query_var' => true,
			'rewrite' => true,
			'public' => true,
			'show_ui' => true
		)
	);
}

/**
 * Provide a taxonomy for Part of Speech (POS) in an online dictionary.
 */
function register_part_of_speech_taxonomy ()
{
	$labels = array(
		'name' =>  _x( 'Part of Speech', 'taxonomy general name' ),
		'singular_name' => _x( 'Part of Speech', 'taxonomy singular name' ),
		'search_items' =>  __( 'Parts of Speech' ),
		'all_items' => __( 'All Parts of Speech' ),
		'parent_item' => __( 'Parent Part of Speech' ),
		'parent_item_colon' => __( 'Parent Part of Speech:' ),
		'edit_item' => __( 'Edit Part of Speech' ),
		'update_item' => __( 'Update Part of Speech' ),
		'add_new_item' => __( 'Add New Part of Speech' ),
		'new_item_name' => __( 'New Part of Speech Name' ),
		'menu_name' => __( "Parts of Speech"),
	);

	register_taxonomy(
		'sil_parts_of_speech',
		'post',
		array(
			'hierarchical' => false,
			'labels' => $labels,
			'update_count_callback' => '_update_post_term_count',
			'query_var' => true,
			'rewrite' => true,
			'public' => true,
			'show_ui' => true
		)
	);
}

/**
 * Provide a taxonomy for the Language Selection in an online dictionary.
 */
function register_language_taxonomy ()
{
	$labels = array(
		'name' => _x( 'Languages', 'taxonomy general name' ),
		'singular_name' => _x( 'Language', 'taxonomy singular name' ),
		'search_items' =>  __( 'Language' ),
		'all_items' => __( 'All Languages' ),
		'parent_item' => __( 'Parent Language' ),
		'parent_item_colon' => __( 'Parent Language:' ),
		'edit_item' => __( 'Edit Language' ),
		'update_item' => __( 'Update Language' ),
		'add_new_item' => __( 'Add New Language' ),
		'new_item_name' => __( 'New Language Name' ),
		'menu_name' => __( 'Language' ),
	);

	register_taxonomy(
		'sil_writing_systems',
		'post',
		array(
			'hierarchical' => false,
			'labels' => $labels,
			'update_count_callback' => '_update_post_term_count',
			'query_var' => true,
			'rewrite' => true,
			'public' => true,
			'show_ui' => true
		)
	);
}

/**
 * Provide a taxonomy for strings that need translation
 */
function register_webstrings_taxonomy ()
{
	$labels = array(
		'name' => _x( 'Website strings', 'taxonomy general name' ),
		'singular_name' => _x( 'Website strings', 'taxonomy singular name' ),
		'search_items' =>  __( 'Website strings' ),
		'all_items' => __( 'All Website strings' ),
		'parent_item' => __( 'Parent Website strings' ),
		'parent_item_colon' => __( 'Parent Website strings:' ),
		'edit_item' => __( 'Edit Website strings' ),
		'update_item' => __( 'Update Website strings' ),
		'add_new_item' => __( 'Add New Website strings' ),
		'new_item_name' => __( 'New Website strings Name' ),
		'menu_name' => __( 'Website strings' ),
	);

	register_taxonomy(
		'sil_webstrings',
		'post',
		array(
			'hierarchical' => false,
			'labels' => $labels,
			'update_count_callback' => '_update_post_term_count',
			'query_var' => true,
			'rewrite' => true,
			'public' => true,
			'show_ui' => true
		)
	);
}

function remove_double_terms ()
{
	global $wpdb;

	//This deals specifically with the problem that languages (sil_writing_systems) sometimes get inserted several times
	/** @noinspection SqlResolve */
	$sql = <<<SQL
DELETE t1 FROM {$wpdb->terms} t1, {$wpdb->terms} t2
WHERE t1.term_id < t2.term_id AND t1.slug = t2.slug
SQL;

	$wpdb->query($sql);
}

/**
 * Uninstall the custom infrastructure set up here by the plugin
 * @param null $delete_taxonomies
 */
function clean_out_dictionary_data ($delete_taxonomies = null)
{

	if (is_plugin_active('wp-super-cache/wp-cache.php')) {
		prune_super_cache(get_supercache_dir(), true);
	}

	if($delete_taxonomies == null)
		$delete_taxonomies = $_POST['delete_taxonomies'];

	//deletes the xhtml file, if still there because import didn't get completed
	$import = new Webonary_Pathway_Xhtml_Import();
	$file = $import->get_latest_xhtml_file();
	if(isset($file->ID))
		wp_delete_attachment($file->ID);

	// Remove all the old dictionary entries.
	remove_entries();

	//delete options
	delete_option("reversal1_langcode");
	delete_option("reversal1_alphabet");
	delete_option("reversal2_langcode");
	delete_option("reversal2_alphabet");
	delete_option("reversal3_langcode");
	delete_option("reversal3_alphabet");

	// Uninstall the custom table(s) and taxonomies.
	if ($delete_taxonomies == 1)
		unregister_custom_taxonomies();

	// Reinstall custom table(s) and taxonomies.
	create_search_tables();
	if ($delete_taxonomies == 1) {
		register_semantic_domains_taxonomy();
		register_part_of_speech_taxonomy();
		register_language_taxonomy();
	}
 }

/**
 * Remove all posts and revisions, leaving other post types
 *
 * @param null $pinged
 * @global $wpdb
 */
function remove_entries($pinged = null)
{
	global $wpdb;

	//just posts in category "webonary"
	/** @noinspection SqlResolve */
	$sql = <<<SQL
DELETE p.*
FROM {$wpdb->posts} AS p
    INNER JOIN {$wpdb->term_relationships} AS r ON p.id = r.object_id
    INNER JOIN {$wpdb->term_taxonomy} AS x ON r.term_taxonomy_id = x.term_taxonomy_id
    INNER JOIN {$wpdb->terms} AS t ON x.term_id = t.term_id
WHERE t.slug = 'webonary' AND p.post_type IN ('post', 'revision')
SQL;

	if(isset($pinged))
		$sql .= " AND p.pinged = '{$pinged}'";

	$wpdb->query($sql);

	$sql = 'DROP TABLE IF EXISTS ' . SEARCHTABLE;
	$wpdb->query($sql);

	$sql = 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'sil_reversal';
	$wpdb->query($sql);

	$sql = 'DROP TABLE IF EXISTS ' . REVERSALTABLE;
	$wpdb->query($sql);

	create_reversal_tables();

	$sql = <<<SQL
DELETE r.*
FROM {$wpdb->term_relationships} AS r
    INNER JOIN {$wpdb->term_taxonomy} AS x ON r.term_taxonomy_id = x.term_taxonomy_id
    INNER JOIN {$wpdb->terms} AS t ON x.term_id = t.term_id
WHERE t.slug = 'webonary'
SQL;

	if(isset($pinged))
		$sql .= " AND r.object_id NOT IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = 'post')";

	$wpdb->query( $sql );
}

function set_options ()
{
	global $wpdb;

	/** @noinspection SqlResolve */
	$sql = <<<SQL
UPDATE {$wpdb->prefix}options
SET option_value = 0
WHERE option_name = 'uploads_use_yearmonth_folders'
SQL;

	include_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	/*
	 * setting the upload_path to blogs.dir will cause problems with newer versions of Wordpress and is unnessary
	if ( is_multisite() )
	{
		$sql = "UPDATE " . $wpdb->prefix . "options " .
				" SET option_value = 'wp-content/blogs.dir/" . $blog_id . "/files' " .
				" WHERE option_name = 'upload_path'";

		dbDelta( $sql );
	}
	*/
}

function set_field_sortorder()
{
	global $wpdb;

	$sql = 'SHOW columns FROM ' . $wpdb->prefix . 'sil_search WHERE Field = \'sortorder\'';
	if($wpdb->get_row($sql))
		return false;

	/** @noinspection SqlResolve */
	$sql = " ALTER TABLE " . $wpdb->prefix . "sil_search ADD sortorder INT NOT NULL DEFAULT  '0'";

	$wpdb->query($sql);

	return true;
}

function strrpos_count($haystack, $needle, $count)
{
	if($count <= 0)
		return false;

	$len = strlen($haystack);
	$pos = $len;

	for($i = 0; $i < $count && $pos; $i++)
		$pos = strrpos($haystack, $needle, $pos - $len - 1);

	return $pos;
}

/**
 * Uninstall custom taxonomies set up here by the plugin.
 */
function unregister_custom_taxonomies ()
{
	global $wpdb;

	/** @noinspection SqlResolve */
	$sql = "UPDATE {$wpdb->term_taxonomy} SET count = 1 WHERE count = 0";
	$wpdb->query( $sql);

	unregister_custom_taxonomy ( 'sil_semantic_domains' );
	unregister_custom_taxonomy ( 'sil_parts_of_speech' );
	unregister_custom_taxonomy ( 'sil_writing_systems' );
	unregister_custom_taxonomy ( 'sil_webstrings' );

	//delete all relationships
	/** @noinspection SqlResolve */
	$del = "DELETE FROM $wpdb->term_relationships WHERE term_taxonomy_id = 1 ";
	$wpdb->query( $del);
}

/**
 * Remove a custom (not builtin) taxonomy.
 *
 * Adapted from the function `unregister_taxonomy` in /wp-includes/taxonomy.php
 *
 * @param string $taxonomy = The taxonomy to remove
 *
 * @global $wp_taxonomies
 */
function unregister_custom_taxonomy($taxonomy)
{
	if (!taxonomy_exists($taxonomy))
		return;

	$taxonomy_object = get_taxonomy($taxonomy);

	// Do not allow unregistering internal taxonomies.
	if ($taxonomy_object->_builtin)
		return;

	global $wp_taxonomies;

	$taxonomy_object->remove_rewrite_rules();
	$taxonomy_object->remove_hooks();

	// Remove the taxonomy.
	unset($wp_taxonomies[$taxonomy]);

	/**
	 * Fires after a taxonomy is unregistered.
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @since 4.5.0
	 *
	 */
	do_action('unregistered_taxonomy', $taxonomy);

	return;
}

/**
 * Uninstall custom tables, taxonomies, etc. on plugin uninstall
 */
function uninstall_sil_dictionary_infrastructure () {
	clean_out_dictionary_data(1);
}

add_action( 'init', 'install_sil_dictionary_infrastructure', 0 );
