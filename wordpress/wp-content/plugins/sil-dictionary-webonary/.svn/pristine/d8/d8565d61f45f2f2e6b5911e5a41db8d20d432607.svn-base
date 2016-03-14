<?php 
// Check to make sure we can even load an importer.
if ( ! defined( 'WP_LOAD_IMPORTERS' ) )
    return;

// Include the WordPress Importer.
require_once ABSPATH . 'wp-admin/includes/import.php';

if ( ! class_exists('WP_Importer') )  {
    $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
    if ( file_exists( $class_wp_importer ) )
        require_once $class_wp_importer;
}

// One more check.
if ( ! class_exists( 'WP_Importer' ) )
	return;

class class_resync_comments extends WP_Importer 
{
	function start()
	{
		global $wpdb;
		
		echo '<h2>' . __( 'Re-syncing comments', 'sil_dictionary' ) . '</h2>';
		?>
		<DIV ID="flushme">no data</DIV>
		<?php 
		
		$arrComments = $this->get_comments();			

		$entry_counter = 1;
		$entries_count = count($arrComments);
		
		foreach($arrComments as $comment)
		{
			$postid = $this->get_comment_postid($comment->comment_type);
			if(isset($postid))
			{
				$sql = "UPDATE " .  $wpdb->comments . " SET comment_post_ID = " . $postid . " WHERE comment_ID = " . $comment->comment_ID;
				$wpdb->query( $sql );
				
				flush();
				?>
				<SCRIPT type="text/javascript">//<![CDATA[
				d = document.getElementById("flushme");
				info = "Updating commentID <?php echo $comment->comment_ID; ?>";
				
				d.innerHTML = info;
				//]]></SCRIPT>
			<?php 		

			}
		}		
		echo "<p>";
		echo "Finished.";
	}	
	
	function get_comments() {
		global $wpdb;

		$sql = "SELECT comment_ID, comment_type " .
			" FROM " . $wpdb->comments;
				
		return $wpdb->get_results($sql);
	}	
	
	function get_comment_postid($postname)
	{
		global $wpdb;
		
		$sql = "SELECT ID " .
			" FROM " . $wpdb->posts .
			" WHERE post_name = '" . $postname . "'";
				
		$row = $wpdb->get_row( $sql );

		return $row->ID;
		
	}
	
}

/*
 * Register the importer so WordPress knows it exists. Specify the start
 * function as an entry point. Paramaters: $id, $name, $description,
 * $callback.
 */
$comments_resync = new class_resync_comments();

register_importer('comments-resync',
		__('Comments Re-Sync', 'sil_dictionary'),
		__('If you have the comments turned on, you need to re-sync your comments after re-importing of your posts.', 'sil_dictionary'),
		array ($comments_resync, 'start'));
		
function preprocess_comment_add_type( $commentdata ) {

	$commentdata['comment_type'] = basename(get_permalink());

  return $commentdata;
}		
?>