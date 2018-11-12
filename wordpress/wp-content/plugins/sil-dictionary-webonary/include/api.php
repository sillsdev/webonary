<?php
//$testclass = new Slug_Custom_Route();
$apiClass = new Webonary_API_MyType();

add_action( 'rest_api_init', array( $apiClass, 'register_new_routes' ) );

/*
function webonary_api_init() {
	global $webonary_api_mytype;

	$webonary_api_mytype = new Webonary_API_MyType();
	add_filter( 'json_endpoints', array( $webonary_api_mytype, 'register_routes' ) );
}

add_action( 'wp_json_server_before_serve', 'webonary_api_init' );
*/

class Webonary_API_MyType {
	/*
    public function register_routes( $routes ) {
    	//creates a route like this: https://test.webonary.org/wp-json/webonary/import
        $routes['/webonary/import'] = array(
        	array( array( $this, 'import'), WP_JSON_Server::CREATABLE | WP_JSON_Server::ACCEPT_RAW ),
        );

        return $routes;
    }
	*/
    function register_new_routes() {

    	$namespace = 'webonary';
    	$base = 'import';

    	register_rest_route( $namespace, '/' . $base, array(
    			'methods' => 'POST' | WP_REST_Server::CREATABLE,
    			'callback' => array( $this, 'import' ),
    		)
    	);
    }

    /*
    public function import2(WP_REST_Request $request)
    {
    	$this->import($request, true);
    }
	*/
	public function import($_headers, $newAPI = true)
	{
		$username = "";
		$password = "";
		if($newAPI)
		{
			$myHeader = $_headers->get_headers();
			$userstring = base64_decode(str_replace("Basic ", "", $myHeader['authorization'][0]));
			$arrUser = explode(":", $userstring);
			$username = $arrUser[0];
			$password = $arrUser[1];
		}

		$authenticated = $this->verifyAdminPrivileges($username, $password);

		$message = "The export to Webonary is completed.\n";
		$message .= "Go here to configure more settings: " . get_site_url() . "/wp-admin/admin.php?page=webonary";

		if($authenticated){

			$arrDirectory = wp_upload_dir();
			$uploadPath = $arrDirectory['path'];
			$uploadDirectory = $arrDirectory['basedir'];

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
					$fontClass = new fontMonagment();
					$fontClass->set_fontFaces($zipFolderPath . "/configured.css", $uploadPath);

					copy($zipFolderPath . "/configured.css", $uploadPath . "/imported-with-xhtml.css");
					error_log("Renamed configured.css to " . $uploadPath . "/imported-with-xhtml.css");

					if(file_exists($uploadDirectory . "/ProjectDictionaryOverrides.css"))
					{
						copy($zipFolderPath . "/ProjectDictionaryOverrides.css", $uploadPath . "/ProjectDictionaryOverrides.css");
					}
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
					if(file_exists($zipFolderPath . "/AudioVisual"))
					{
						$this->recursiveRemoveDir($uploadPath . "/audio");
						$this->recursiveRemoveDir($uploadPath . "/AudioVisual");
					}
					//then copy everything under AudioVisual and pictures
					$this->recursiveCopy($zipFolderPath . "/AudioVisual", $uploadPath . "/AudioVisual");
					$this->recursiveRemoveDir($zipFolderPath . "/AudioVisual");

					$this->recursiveCopy($zipFolderPath . "/pictures", $uploadPath . "/images/original");
					if(file_exists($zipFolderPath . "/pictures/thumbnail"))
					{
						$this->recursiveCopy($zipFolderPath . "/pictures/thumbnail", $uploadPath . "/images/thumbnail");
					}
					else
					{
						$this->resize_image ( $uploadPath . "/images/original", 96, 96, $uploadPath . "/images/thumbnail" );
					}

					$this->recursiveRemoveDir($zipFolderPath . "/pictures");

				}

				if(isset($xhtmlConfigured))
				{
					//we first delete all existing posts (in category Webonary)
					remove_entries('flexlinks');

					//deletes data that comes with the posts, but gets stored separately (e.g. "parts of speech")
					clean_out_dictionary_data(1);

					$filetype = "configured";
					$xhtmlFileURL = $fileConfigured;

					//This message tells FLEx that the upload dialog can now be closed.
					echo "Upload successful\n";

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
		if (!function_exists('wp_handle_upload'))
		{
			require_once( ABSPATH . '/wp-admin/includes/file.php' );
		}

		$overrides = array( 'test_form' => false, 'test_type' => false );
		$file = wp_handle_upload($zipfile, $overrides);

		if (isset( $file['error']))
		{
			echo "Error: Upload failed: " . $file['error'] . "\n";
			unlink($uploadPath . "/" . $zipfile['name']);
			return false;
		}

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
        } else if (file_exists ( $src )) {
            copy ( $src, $dst );
        	error_log("moved: " . $src . " to " . $dst);
        }
    }

    function resize_image($src, $w, $h, $dst) {

    	if(!file_exists($dst))
    	{
    		mkdir ( $dst );
    	}
    	$files = scandir ( $src );
    	foreach ( $files as $file )
    	{
    		if ($file != "." && $file != "..")
    		{
    			list($width, $height) = getimagesize($src . "/"  . $file);

    			$r = $width / $height;
    			$newwidth = $h*$r;
    			$newheight = $h;

    			if($newheight <= $height && $newwidth <= $width)
    			{
	    			$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

	    			if($ext == "png")
	    			{
	    				$src_image = imagecreatefrompng($src . "/" . $file);
	    			}
	    			elseif($ext == "gif")
	    			{
	    				$src_image = imagecreatefromgif($src . "/" . $file);
	    			}
	    			else
	    			{
	    				$src_image = imagecreatefromjpeg($src . "/" . $file);
	    			}
	    			$dst_image  = imagecreatetruecolor($newwidth, $newheight);
	    			imagecopyresized($dst_image, $src_image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height );

	    			if($ext == "png")
	    			{
	    				imagepng($dst_image, $dst . "/" . $file);
	    			}
	    			elseif($ext == "gif")
	    			{
	    				imagegif($dst_image, $dst . "/" . $file);
	    			}
	    			else
	    			{
	    				imagejpeg($dst_image, $dst . "/" . $file, 90);
	    			}
    			}
    			else
    			{
    				copy ( $src . "/" . $file, $dst . "/" . $file );
    			}
    			echo $dst . "/" . $file . "\n";
    		}
    	}
    }

	public function verifyAdminPrivileges($username, $password)
	{
		global $wpdb;

		if(!empty($username))
		{
			$user = wp_authenticate($username, $password );
		}
		else
		{
			$user = wp_authenticate( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] );
		}


		if(isset($user->ID))
		{
			$sql = "SELECT meta_value AS userrole FROM wp_usermeta " .
				   " WHERE (user_id = " . $user->ID . " AND meta_key = 'wp_" . get_current_blog_id()  . "_capabilities') OR " .
				   	" (user_id = " . $user->ID . " AND meta_key = 'wp_capabilities')";


			$roleSerialized = $wpdb->get_var($sql);
			$userrole = unserialize($roleSerialized);

			if($userrole['administrator'] == true)
			{
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
