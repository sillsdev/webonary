<?php
	if(exec('echo EXEC') == 'EXEC' && file_exists(ABSPATH . "exec-configured.txt"))
	{
		$blogid = get_current_blog_id();
		$command = "php -f " . ABSPATH . "wp-content/plugins/sil-dictionary-webonary/processes/import_entries.php " . ABSPATH . " " . $blogid . " " . $filetype . " " . $xhtmlFileURL;
	
		exec($command . ' > /tmp/webonaryimport_' . $blogid . '.txt 2>&1 &');
	}
	else
	{
		$argv = null;
		require(ABSPATH . "wp-content/plugins/sil-dictionary-webonary/processes/import_entries.php");
	}
?>