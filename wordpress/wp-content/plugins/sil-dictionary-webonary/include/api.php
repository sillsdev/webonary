<?php
function webonary_api_init() {
	global $webonary_api_mytype;

	$webonary_api_mytype = new Webonary_API_MyType();
	add_filter( 'json_endpoints', array( $webonary_api_mytype, 'register_routes' ) );
}

add_action( 'wp_json_server_before_serve', 'webonary_api_init' );

class Webonary_API_MyType {
    public function register_routes( $routes ) {
        $routes['/webonary/import'] = array(
        	array( array( $this, 'import'), WP_JSON_Server::CREATABLE | WP_JSON_Server::ACCEPT_RAW ),
        );

        return $routes;
    }

	public function import($_headers)
	{
		$authenticated = $this->verifyAdminPrivileges($email, $userid);
		
		$message = "The export to Webonary is completed.\n";
		$message .= "Go here to configure more settings: " . get_site_url() . "/wp-admin/admin.php?page=webonary";

		if($authenticated){

			$arrDirectory = wp_upload_dir();
			$uploadPath = $arrDirectory['path'];
			
			$zipFolderPath = $uploadPath . "/" . str_replace(".zip", "", $_FILES['file']['name']);
			
			$unzipped = $this->unzip($_FILES['file'], $uploadPath, $zipFolderPath);

			//program can be closed now, the import will run in the background
			flush();

			if($unzipped)
			{
				$fileConfigured = $zipFolderPath . "/configured.xhtml";
				$xhtmlConfigured = file_get_contents($fileConfigured);

				//moving and renaming configured style sheet file
				if(file_exists($zipFolderPath . "/configured.css"))
				{
					$this->set_fonts_fromCssFile($zipFolderPath . "/configured.css", $uploadPath);
						
					copy($zipFolderPath . "/configured.css", $uploadPath . "/imported-with-xhtml.css");
					error_log("Renamed configured.css to " . $uploadPath . "/imported-with-xhtml.css");
					unlink($zipFolderPath . "/configured.css");
				}
				
				//copy reversal css files
				foreach (glob($zipFolderPath . "/*.css") as $file) {
					$filename = basename($file);
						
					if($filename != "configured.css")
					{
						copy($zipFolderPath . "/" . $filename, $uploadPath . "/". $filename);
						error_log("moved " . $zipFolderPath . "/" . $filename . " to " . $uploadPath . "/" . $filename);
						unlink($file);
					}
				}
				
				//copy folder files (which includes audio and image folders and files)
				if(file_exists($uploadPath))
				{
					//first delete any existing files
					$this->recursiveRemoveDir($uploadPath . "/images/thumbnail");
					$this->recursiveRemoveDir($uploadPath . "/images/original");
					$this->recursiveRemoveDir($uploadPath . "/audio");
					$this->recursiveRemoveDir($uploadPath . "/AudioVisual");
					//then copy everything under AudioVisual and pictures
					$this->recursiveCopy($zipFolderPath . "/AudioVisual", $uploadPath . "/AudioVisual");
					$this->recursiveRemoveDir($zipFolderPath . "/AudioVisual");
					$this->recursiveCopy($zipFolderPath . "/pictures", $uploadPath . "/images/thumbnail");
					$this->recursiveRemoveDir($zipFolderPath . "/pictures");
						
				}

				if(isset($xhtmlConfigured))
				{
					//we first delete all existing posts (in category Webonary)
					remove_entries('flexlinks');
					
					//deletes data that comes with the posts, but gets stored separately (e.g. "parts of speech")
					clean_out_dictionary_data();
	
					$filetype = "configured";
					$xhtmlFileURL = $fileConfigured;
					require("run_import.php");
				}
			}

			//for importing reversal files, see import_entries.php
			//first we need to complete importing configured.xhtml

			if(file_exists($zipFolderPath))
			{
				//deletes the extracted zip folder
				//update 21 April 2015: We no longer remove the directory, instead just the individual files
				//get deleted as we are now running the import in an external process and the files would otherwise be missing
				//$this->recursiveRemoveDir($zipFolderPath);
			}
			
			/* //THIS DOESN'T GET DISPLAYED BY FLEx
			echo "You can now close this window. Import is running in the background...\n";
			echo "Go to this website to view the import progress: " . get_site_url() . "/wp-admin/admin.php?page=webonary\n\n";
			echo "You will receive an email when the import has completed.\n";
			*/
			return "";
		}
		else
		{
			echo "authentication failed\n";
			flush();
		}

		return;
	}

	// Receive upload. Unzip it to uploadPath. Remove upload file.
	public function unzip($zipfile, $uploadPath, $zipFolderPath)
	{
		$overrides = array( 'test_form' => false, 'test_type' => false );
		$file = wp_handle_upload($zipfile, $overrides);

		if (isset( $file['error']))
		{
			echo "Error: Upload failed: " . $file['error'] . "\n";
			unlink($uploadPath . "/" . $zipfile['name']);
			return false;
		}

		echo "Upload successful\n";
		
		error_log("zip file: " . $uploadPath . "/" . $_FILES['file']['name']);
		error_log(WP_CONTENT_DIR . "/archives");
		
		if(file_exists(WP_CONTENT_DIR . "/archives"))
		{
			error_log("copy zip file");
			copy($uploadPath . "/" . $_FILES['file']['name'], WP_CONTENT_DIR . "/archives/" . $_FILES['file']['name']);
		}

		$zip = new ZipArchive;
		$res = $zip->open($uploadPath . "/" . $zipfile['name']);
		if ($res === FALSE)
		{
			echo "Error: " . $zipfile['name'] . " isn't a valid zip file\n";
			unlink($uploadPath . "/" . $zipfile['name']);
			return false;
		}

		$unzip_success = $zip->extractTo($zipFolderPath);
		$zip->close();
		if(!$unzip_success)
		{
			echo "Error: couldn't extract zip file to " . $uploadPath;
			unlink($uploadPath . "/" . $zipfile['name']);
			return false;
		}

		error_log("zip file extracted");
		echo "zip file extracted successfully\n";
		unlink($uploadPath . "/" . $zipfile['name']);
		return true;
	}

	// Function to remove folders and files
    function recursiveRemoveDir($dir) {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file)
            {
                if ($file != "." && $file != "..") $this->recursiveRemoveDir("$dir/$file");
                error_log("removed: " . $dir);
            	rmdir($dir);
            }
        }
        else if (file_exists($dir)) unlink($dir);
    }

	// Function to Copy folders and files
    function recursiveCopy($src, $dst) {
        if (is_dir ( $src )) {
        	if(!file_exists($dst))
        	{
        		error_log("create folder: " . $dst);
            	mkdir ( $dst );
        	}
            $files = scandir ( $src );
            foreach ( $files as $file )
                if ($file != "." && $file != "..")
                    $this->recursiveCopy ( "$src/$file", "$dst/$file" );
        } else if (file_exists ( $src ))
            copy ( $src, $dst );
        	error_log("moved: " . $src . " to " . $dst);
    }

    public function set_fonts_fromCssFile($file = "configured.css", $uploadPath)
    {
    	$css_string = file_get_contents($file);

    	$arrFontName[0] = null;
    	$arrFontStorage[0] = null;
    	 
    	$arrFontName[1] = "Charis SIL";
    	$arrFontStorage[1] = "CharisSIL";
    	
    	$arrFontName[2] = "Charis SIL Compact";
    	$arrFontStorage[2] = "CharisSIL";
    	
    	$arrFontName[3] = "Andika";
    	$arrFontStorage[3] = "Andika";
    	
    	// Get the CSS that contains a font-family rule.
    	$length = strlen($css_string);
    	$porperty = 'font-family';
    	$replacements = array();
    	$x = 0;
    	while (($last_position = strpos($css_string, $porperty, $last_position)) !== FALSE) {
    		// Get closing bracket.
    		$end = strpos($css_string, '}', $last_position);
    		if ($end === FALSE) {
    			$end = $length;
    		}
    		$end++;
    
    		// Get position of the last closing bracket (start of this section).
    		$start = strrpos($css_string, '}', - ($length - $last_position));
    		if ($start === FALSE) {
    			$start = 0;
    		}
    		else {
    			$start++;
    		}
    
    		// Get closing ; in order to get the end of the declaration.
    		$declaration_end = strpos($css_string, ';', $last_position);
    
    		// Get values.
    		$start_of_values = strpos($css_string, ':', $last_position);
    		$values_string = substr($css_string, $start_of_values + 1, $declaration_end - ($start_of_values + 1));
    		// Parse values string into an array of values.
    		$values_array = explode(',', $values_string);
    
    		$arrCSSFonts[$x] = str_replace("'", "", $values_array[0]);
    
    		// Values array has more than 1 value and first element is a quoted string.
    
    		// Advance position.
    		$x++;
    		$last_position = $end;
    	}
    	$arrUniqueCSSFonts = array_unique($arrCSSFonts);
    	
    	$fontFace = "";
    	$arrFontStyles = array("R", "B", "I", "BI");
    	foreach($arrUniqueCSSFonts as $userFont)
    	{
			$fontKey = array_search($userFont, $arrFontName);
			
			if($fontKey > 0)
			{
				foreach($arrFontStyles as $fontStyle)
				{
					//echo WP_CONTENT_DIR . "/uploads/font/" . $arrFontStorage[$fontKey] . "-" . $fontStyle . ".woff\n";
					if(file_exists(WP_CONTENT_DIR . "/uploads/fonts/" . $arrFontStorage[$fontKey] . "-" . $fontStyle . ".woff"))
					{
						$fontFace .= "@font-face {\n";
						$fontFace .= "font-family: " . $userFont . ";\n";
						$fontFace .= "src: url(/wp-content/uploads/fonts/" . $arrFontStorage[$fontKey] . "-" . $fontStyle . ".woff)\n";
						if($fontStyle == "B" || $fontFace == "BI")
						{
							$fontFace .= "font-weight: bold;\n";
						}
						if($fontStyle == "I" || $fontFace == "BI")
						{
							$fontFace .= "font-style: italic;\n";
						}
						$fontFace .= "}\n\n";
					}
				}
			}
    	}
    	file_put_contents($uploadPath . "/custom.css" , $fontFace);
    	
    	return;
    }
    
	public function verifyAdminPrivileges(&$email = "", &$userid = 0)
	{
		global $wpdb;

		$user = wp_authenticate( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] );

		if(isset($user->ID))
		{
			$sql = "SELECT meta_value AS userrole FROM wp_usermeta " .
				   " WHERE user_id = " . $user->ID . " AND meta_key = 'wp_" . get_current_blog_id()  . "_capabilities'";


			$roleSerialized = $wpdb->get_var($sql);
			$userrole = unserialize($roleSerialized);

			if($userrole['administrator'] == true)
			{
				$user_info = get_userdata($user->ID);
				$userid = $user->ID;
				$email = $user_info->user_email;
				return true;
			}
			else
			{
				echo "User doesn't have permission to import data to this Webonary site\n";
				return false;
			}

		}
		else
		{
			echo "Wrong username or password.\n";
			return false;
		}
	}
}
