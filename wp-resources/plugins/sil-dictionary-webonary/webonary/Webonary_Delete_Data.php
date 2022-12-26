<?php

class Webonary_Delete_Data
{
	/**
	 * Uninstall the custom infrastructure set up here by the plugin
	 *
	 * @param bool $delete_taxonomies
	 * @return array
	 * @throws Exception
	 */
	public static function DeleteDictionaryData(bool $delete_taxonomies = false): array
	{
		if (is_plugin_active('wp-super-cache/wp-cache.php')) {
			prune_super_cache(get_supercache_dir(), true);
		}

		if (!$delete_taxonomies)
			$delete_taxonomies = (bool)($_POST['delete_taxonomies'] ?? false);

		if (get_option('useCloudBackend'))
			return Webonary_Cloud::deleteDictionaryData(Webonary_Cloud::getBlogDictionaryId());

		return self::DeleteMySqlDictionaryData($delete_taxonomies);
	}

	private static function DeleteMySqlDictionaryData(bool $delete_taxonomies): array
	{
		//deletes the xhtml file, if still there because import didn't get completed
		$import = new Webonary_Pathway_Xhtml_Import();
		$file = $import->get_latest_xhtml_file();
		if (isset($file->ID))
			wp_delete_attachment($file->ID);

		// Remove all the old dictionary entries.
		self::RemoveEntries();

		//delete options
		delete_option("reversal1_langcode");
		delete_option("reversal1_alphabet");
		delete_option("reversal2_langcode");
		delete_option("reversal2_alphabet");
		delete_option("reversal3_langcode");
		delete_option("reversal3_alphabet");

		// Uninstall the custom table(s) and taxonomies.
		if ($delete_taxonomies)
			self::UnregisterCustomTaxonomies();

		// Reinstall custom table(s) and taxonomies.
		Webonary_Infrastructure::CreateSearchTables();
		if ($delete_taxonomies) {
			Webonary_Infrastructure::RegisterSemanticDomainsTaxonomy();
			Webonary_Infrastructure::RegisterPartOfSpeechTaxonomy();
			Webonary_Infrastructure::RegisterLanguageTaxonomy();
			Webonary_Infrastructure::RegisterWebStringsTaxonomy();
		}

		return ['deleted' => 1, 'msg' => __('Finished deleting Webonary data', 'sil_dictionary')];
	}

	/** @noinspection SqlResolve */
	public static function RemoveEntries(?string $pinged = null): void
	{
		global $wpdb;

		//just posts in category "webonary"
		/** @noinspection SqlResolve */
		$sql = <<<SQL
DELETE p.*
FROM $wpdb->posts AS p
    INNER JOIN $wpdb->term_relationships AS r ON p.id = r.object_id
    INNER JOIN $wpdb->term_taxonomy AS x ON r.term_taxonomy_id = x.term_taxonomy_id
    INNER JOIN $wpdb->terms AS t ON x.term_id = t.term_id
WHERE t.slug = 'webonary' AND p.post_type IN ('post', 'revision')
SQL;

		if (!empty($pinged))
			$sql .= $wpdb->prepare($sql . ' AND p.pinged = %s', $pinged);

		$wpdb->query($sql);

		$sql = 'DROP TABLE IF EXISTS ' . SEARCHTABLE;
		$wpdb->query($sql);

		$sql = 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'sil_reversal';
		$wpdb->query($sql);

		$sql = 'DROP TABLE IF EXISTS ' . REVERSALTABLE;
		$wpdb->query($sql);

		Webonary_Infrastructure::CreateReversalTables();

		$sql = <<<SQL
DELETE r.*
FROM $wpdb->term_relationships AS r
    INNER JOIN $wpdb->term_taxonomy AS x ON r.term_taxonomy_id = x.term_taxonomy_id
    INNER JOIN $wpdb->terms AS t ON x.term_id = t.term_id
WHERE t.slug = 'webonary'
SQL;

		if (isset($pinged))
			$sql .= " AND r.object_id NOT IN (SELECT ID FROM $wpdb->posts WHERE post_type = 'post')";

		$wpdb->query($sql);
	}


	public static function UnregisterCustomTaxonomies(): void
	{
		global $wpdb;

		/** @noinspection SqlResolve */
		$sql = "UPDATE $wpdb->term_taxonomy SET count = 1 WHERE count = 0";
		$wpdb->query($sql);

		self::UnregisterCustomTaxonomy('sil_semantic_domains');
		self::UnregisterCustomTaxonomy('sil_parts_of_speech');
		self::UnregisterCustomTaxonomy('sil_writing_systems');
		self::UnregisterCustomTaxonomy('sil_webstrings');

		//delete all relationships
		/** @noinspection SqlResolve */
		$del = "DELETE FROM $wpdb->term_relationships WHERE term_taxonomy_id = 1 ";
		$wpdb->query($del);
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
	public static function UnregisterCustomTaxonomy(string $taxonomy): void
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
	}
}