<?php
//$testclass = new Slug_Custom_Route();
$apiClass = new Webonary_API_MyType();

add_action( 'rest_api_init', array( $apiClass, 'register_new_routes' ) );

class Webonary_API_MyType {

	function register_new_routes() {

    	$namespace = 'webonary';

    	register_rest_route( $namespace, '/import', array(
    			'methods' => 'POST' | WP_REST_Server::CREATABLE,
    			'callback' => array( $this, 'import' ),
    		)
    	);

    	//this allows one to make a query like this:
    	//http://webonary.localhost/lubwisi/wp-json/webonary/query/dog/en
    	//language parameter is optional
    	register_rest_route( $namespace, '/query/(?P<term>\w+)(?:/(?P<lang>\w+))?', array(
    			'methods' => 'GET' | WP_REST_Server::READABLE,
    			'callback' => array( $this, 'query' ),
    			'args'                => array(),
		    )
    	);

    }

    public function query($request)
    {
    	$data = get_indexed_entries($request['term'], $request['lang']);

    	return new WP_REST_Response( $data, 200 );
    }

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

		$user = Webonary_Utility::verifyAdminPrivileges($username, $password);

		$message = "The export to Webonary is completed.\n";
		$message .= "Go here to configure more settings: " . get_site_url() . "/wp-admin/admin.php?page=webonary";

		if($user){
			$userid = $user->ID;

			$arrDirectory = wp_upload_dir();
			$uploadPath = $arrDirectory['path'];
			$uploadDirectory = $arrDirectory['basedir'];

			$zipFolderPath = $uploadPath . "/" . str_replace(".zip", "", $_FILES['file']['name']);

			$unzipped = Webonary_Utility::unzip($_FILES['file'], $uploadPath, $zipFolderPath);

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
					Webonary_Utility::recursiveRemoveDir($uploadPath . "/images/thumbnail");
					Webonary_Utility::recursiveRemoveDir($uploadPath . "/images/original");
					if(file_exists($zipFolderPath . "/AudioVisual"))
					{
						Webonary_Utility::recursiveRemoveDir($uploadPath . "/audio");
						Webonary_Utility::recursiveRemoveDir($uploadPath . "/AudioVisual");
					}
					//then copy everything under AudioVisual and pictures
					Webonary_Utility::recursiveCopy($zipFolderPath . "/AudioVisual", $uploadPath . "/AudioVisual");
					Webonary_Utility::recursiveRemoveDir($zipFolderPath . "/AudioVisual");

					Webonary_Utility::recursiveCopy($zipFolderPath . "/pictures", $uploadPath . "/images/original");
					if(file_exists($zipFolderPath . "/pictures/thumbnail"))
					{
						Webonary_Utility::recursiveCopy($zipFolderPath . "/pictures/thumbnail", $uploadPath . "/images/thumbnail");
					}
					else
					{
						Webonary_Utility::resize_image ( $uploadPath . "/images/original", 150, 150, $uploadPath . "/images/thumbnail" );
					}

					Webonary_Utility::recursiveRemoveDir($zipFolderPath . "/pictures");

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
			$message = "authentication failed\n";
			flush();
		}

		return;
	}
}
