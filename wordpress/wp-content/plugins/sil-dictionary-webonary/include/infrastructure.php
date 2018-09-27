<?php
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


//---------------------------------------------------------------------------//

/**
 * Install the SIL dictionary infrastructure if needed.
 */
function install_sil_dictionary_infrastructure() {
	global $wpdb;
	global $blog_id;

	$sql = "SELECT DATABASE();";
	$dbName = $wpdb->get_var($sql);

	$sql = "select COLLATION_NAME from information_schema.columns where TABLE_SCHEMA = '" . $dbName . "' and TABLE_NAME = '". $wpdb->prefix . "posts' and COLUMN_NAME = 'post_title'";

	$postsCollation = $wpdb->get_var($sql);

	// The collation for all webonary databases is required to be utf8mb4_general_ci on all versions of mySQL that support it.

	define('COLLATION', $wpdb->charset);
	define('FULLCOLLATION', $wpdb->collate);

	/*
	if (version_compare($wpdb->db_version(), '5.5.3') >= 0)
	{
		// Review: The forced use of collation is fragile, and unlikely to do what is expected. Certainly their are many combinations that are not compatible. CP 2017-02
		// Proposal: Remove all forced collation and pre populate index exemplars CP 2017-02
		define('COLLATION', "UTF8MB4");
		define('FULLCOLLATION', "utf8mb4_general_ci");
	}
	else
	{
		define('COLLATION', "UTF8");
		define('FULLCOLLATION', "utf8_general_ci");
	}
	*/
	if(is_admin())
	{
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

//---------------------------------------------------------------------------//
function create_reversal_tables () {
	global $wpdb;

	$table = REVERSALTABLE;
	$sql = "CREATE TABLE `" . $table . "` (
		`id` varchar(50) NOT NULL,
		`language_code` varchar(20) CHARACTER SET " . COLLATION . " COLLATE " . FULLCOLLATION . ",
		`reversal_head` longtext CHARACTER SET " . COLLATION . " COLLATE " . FULLCOLLATION . ",
		`reversal_content` longtext CHARACTER SET " . COLLATION . " COLLATE " . FULLCOLLATION . ",
		`sortorder` INT NOT NULL DEFAULT '0',
		`browseletter` varchar(5) CHARACTER SET " . COLLATION . " COLLATE " . FULLCOLLATION .
		$sql .= ", UNIQUE KEY (`id`)";
		$sql .= ") CHARACTER SET " . COLLATION . " COLLATE " . FULLCOLLATION . ";";

	//echo $sql . "<br>";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

function create_search_tables () {
	global $wpdb;

	$table = SEARCHTABLE;
	$sql = "CREATE TABLE `" . $table . "` (
		`post_id` bigint(20) NOT NULL,
		`language_code` varchar(20),
		`relevance` tinyint,
		`search_strings` longtext,
		`subid` INT NOT NULL DEFAULT  '0',
		`sortorder` INT NOT NULL DEFAULT '0',
		PRIMARY KEY  (`post_id`, `language_code`, `relevance`, `search_strings` ( 150 )),
		INDEX relevance_idx (relevance)
		) CHARACTER SET " . COLLATION . " COLLATE " . FULLCOLLATION . ";";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

//---------------------------------------------------------------------------//

/**
 * Provide a taxonomy for semantic domains in an online dictionary.
 */

function register_semantic_domains_taxonomy () {

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
    ) ;
}

//-----------------------------------------------------------------------------//

/**
 * Provide a taxonomy for Part of Speech (POS) in an online dictionary.
 */


function register_part_of_speech_taxonomy () {

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
    ) ;
}

//-----------------------------------------------------------------------------//

/**
 * Provide a taxonomy for the Language Selection in an online dictionary.
 */

function register_language_taxonomy () {

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
    ) ;
}

//-----------------------------------------------------------------------------//

/**
 * Provide a taxonomy for strings that need translation
 */

function register_webstrings_taxonomy () {

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
    ) ;
}

function remove_double_terms () {
	global $wpdb;

	//This deals specifically with the problem that languages (sil_writing_systems) sometimes get inserted several times
	$sql = "DELETE t1 FROM $wpdb->terms t1, $wpdb->terms t2
			WHERE t1.term_id < t2.term_id AND t1.slug = t2.slug";

	$wpdb->query( $sql );
}

//-----------------------------------------------------------------------------//

/**
 * Uninstall the custom infrastsructure set up here by the plugin
 */

function clean_out_dictionary_data ($delete_taxonomies = null) {

	if ( is_plugin_active( 'wp-super-cache/wp-cache.php' ) )
	{
		prune_super_cache( get_supercache_dir(), true );
	}

	if($delete_taxonomies == null)
	{
		$delete_taxonomies = $_POST['delete_taxonomies'];
	}

	//deletes the xhtml file, if still there because import didn't get completed
	$import = new sil_pathway_xhtml_Import();
	$file = $import->get_latest_xhtmlfile();
	if(isset($file->ID))
	{
		wp_delete_attachment( $file->ID );
	}

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

//-----------------------------------------------------------------------------//

/**
 * Remove all posts and revisions, leaving other post types
 * @global  $wpdb
 * @return <type>
 */

function remove_entries ($pinged = null) {
	global $wpdb;

	$import = new sil_pathway_xhtml_Import();

	$catid = $import->get_category_id();

	//just posts in category "webonary"
	$sql = "DELETE FROM " . $wpdb->prefix . "posts " .
	" WHERE post_type IN ('post', 'revision') AND " .
	" ID IN (SELECT object_id FROM " . $wpdb->prefix . "term_relationships WHERE " . $wpdb->prefix . "term_relationships.term_taxonomy_id = " . $catid .")";
	if(isset($pinged))
	{
		$sql .= " AND pinged = '" . $pinged . "'";
	}

	$wpdb->query( $sql );

	$sql = "DROP TABLE IF EXISTS " . $wpdb->prefix . "sil_search";
	$wpdb->query( $sql );


	$sql = "DROP TABLE IF EXISTS " . $wpdb->prefix . "sil_reversal";
	$wpdb->query( $sql );

	$sql = "DROP TABLE IF EXISTS " . $wpdb->prefix . "sil_reversals";
	$wpdb->query( $sql );

	create_reversal_tables();

	$sql = "DELETE FROM " . $wpdb->prefix . "term_relationships WHERE term_taxonomy_id = " . $catid;
	if(isset($pinged))
	{
		$sql .= " AND object_id NOT IN (SELECT ID FROM $wpdb->posts WHERE post_type = 'post')";
	}

	$return_value = $wpdb->get_var( $sql );
}

//-----------------------------------------------------------------------------//

function set_options () {
	global $wpdb;
	global $blog_id;

	$sql = "UPDATE " . $wpdb->prefix . "options " .
		 " SET option_value = 0 " .
		 " WHERE option_name = 'uploads_use_yearmonth_folders'";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
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

function set_field_sortorder() {
	global $wpdb;

	$sql = " SHOW columns FROM " . $wpdb->prefix . "sil_search WHERE Field = 'sortorder'";
	if($wpdb->get_row($sql))
	{
		return false;
	}
	$sql = " ALTER TABLE " . $wpdb->prefix . "sil_search ADD sortorder INT NOT NULL DEFAULT  '0'";

	$wpdb->query( $sql );
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

function unregister_custom_taxonomies () {
	global $wpdb;

	$sql = "UPDATE $wpdb->term_taxonomy SET count = 1 WHERE count = 0";
	$wpdb->query( $sql);

	unregister_custom_taxonomy ( 'sil_semantic_domains' );
	unregister_custom_taxonomy ( 'sil_parts_of_speech' );
	unregister_custom_taxonomy ( 'sil_writing_systems' );
	unregister_custom_taxonomy ( 'sil_webstrings' );

	//delete all relationships
	$del = "DELETE FROM $wpdb->term_relationships WHERE term_taxonomy_id = 1 ";
	$wpdb->query( $del);
}

//-----------------------------------------------------------------------------//

/**
 * Remove a custom (not builtin) taxonomy.
 * @global <type> $wp_taxonomies
 * @param <string> $taxonomy = The taxonomy to remove
 * @link http://core.trac.wordpress.org/ticket/12629
 */

/*
 * This code may well be deprecated soon, as it is currently a feature request.
 * See the link above.
 */

function unregister_custom_taxonomy ( $taxonomy ) {
	global $wp_taxonomies;
	if ( ! $taxonomy->builtin ) {
		$terms = get_terms( $taxonomy );
		foreach ( $terms as $term ) {
			wp_delete_term( $term->term_id, $taxonomy );
		}
	unset( $wp_taxonomies[$taxonomy]);
	}
}


//-----------------------------------------------------------------------------//

/**
 * Unistall custom tables, taxonomies, etc. on plugin uninstall
 */
function uninstall_sil_dictionary_infrastructure () {
	clean_out_dictionary_data(1);
}

add_action( 'init', 'install_sil_dictionary_infrastructure', 0 );


?>