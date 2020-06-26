<?php


class Webonary_API_MyType
{
	public static function Register_New_Routes()
	{
		$namespace = 'webonary';

		register_rest_route($namespace, '/import', array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => 'Webonary_API_MyType::Import'
			)
		);

		//this allows one to make a query like this:
		//http://webonary.localhost/lubwisi/wp-json/webonary/query/dog/en
		//language parameter is optional
		register_rest_route($namespace, '/query/(?P<term>\w+)(?:/(?P<lang>\w+))?', array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => 'Webonary_API_MyType::Query',
				'args' => array(),
			)
		);
	}

	public static function Query($request)
	{
		$data = get_indexed_entries($request['term'], $request['lang']);

		return new WP_REST_Response($data, 200);
	}

	/**
	 * @param WP_REST_Request $_headers
	 * @param bool $newAPI
	 */
	public static function Import($_headers, $newAPI = true)
	{
		$username = '';
		$password = '';
		if ($newAPI) {
			$myHeader = $_headers->get_headers();

			if (isset($myHeader['authorization'][0])) {
				$user_string = base64_decode(str_replace('Basic ', '', $myHeader['authorization'][0]));
				$arrUser = explode(':', $user_string, 2);

				if (count($arrUser) == 2) {
					$username = $arrUser[0];
					$password = $arrUser[1];
				}
			}
		}

		$user = Webonary_Utility::verifyAdminPrivileges($username, $password);

		if (empty($user)) {
			flush();
			return;
		}

		$arrDirectory = wp_upload_dir();
		$uploadPath = $arrDirectory['path'];
		$uploadDirectory = $arrDirectory['basedir'];

		$zipFolderPath = $uploadPath . '/' . str_replace('.zip', '', $_FILES['file']['name']);

		$unzipped = Webonary_Utility::unzip($_FILES['file'], $uploadPath, $zipFolderPath);

		if (!$unzipped) {
			flush();
			return;
		}

		$fileConfigured = $zipFolderPath . '/configured.xhtml';
		$xhtmlConfigured = file_get_contents($fileConfigured);

		//moving and renaming configured style sheet file
		if (file_exists($zipFolderPath . '/configured.css')) {
			$fontClass = new Webonary_Font_Management();
			$css_string = file_get_contents($zipFolderPath . '/configured.css');
			$fontClass->set_fontFaces($css_string, $uploadPath);

			copy($zipFolderPath . '/configured.css', $uploadPath . '/imported-with-xhtml.css');
			
			if (file_exists($uploadDirectory . '/ProjectDictionaryOverrides.css')) {
				copy($zipFolderPath . '/ProjectDictionaryOverrides.css', $uploadPath . '/ProjectDictionaryOverrides.css');
			}
			unlink($zipFolderPath . '/configured.css');
		}

		//copy reversal css files
		foreach (glob($zipFolderPath . '/*.css') as $file) {
			$filename = basename($file);

			if ($filename != 'configured.css') {
				$newFilename = str_replace('-', '_', $filename);
				copy($zipFolderPath . '/' . $filename, $uploadPath . '/' . $newFilename);
				unlink($file);
			}
		}

		//copy folder files (which includes audio and image folders and files)
		if (is_dir($uploadPath)) {
			//first delete any existing files
			Webonary_Utility::recursiveRemoveDir($uploadPath . '/images/thumbnail');
			Webonary_Utility::recursiveRemoveDir($uploadPath . '/images/original');
			if (is_dir($zipFolderPath . '/AudioVisual')) {
				Webonary_Utility::recursiveRemoveDir($uploadPath . '/audio');
				Webonary_Utility::recursiveRemoveDir($uploadPath . '/AudioVisual');
			}
			//then copy everything under AudioVisual and pictures
			Webonary_Utility::recursiveCopy($zipFolderPath . '/AudioVisual', $uploadPath . '/AudioVisual');
			Webonary_Utility::recursiveRemoveDir($zipFolderPath . '/AudioVisual');

			Webonary_Utility::recursiveCopy($zipFolderPath . '/pictures', $uploadPath . '/images/original');
			if (is_dir($zipFolderPath . '/pictures/thumbnail')) {
				Webonary_Utility::recursiveCopy($zipFolderPath . '/pictures/thumbnail', $uploadPath . '/images/thumbnail');
			} else {
				Webonary_Utility::resize_image($uploadPath . '/images/original', 150, 150, $uploadPath . '/images/thumbnail');
			}

			Webonary_Utility::recursiveRemoveDir($zipFolderPath . '/pictures');
		}

		if (!isset($xhtmlConfigured)) {
			flush();
			return;
		}


		//we first delete all existing posts (in category Webonary)
		remove_entries('flexlinks');

		//deletes data that comes with the posts, but gets stored separately (e.g. 'parts of speech')
		clean_out_dictionary_data(1);

		// attempt to clear all buffered output so we can close the connection and release the client
		$cleared = Webonary_Utility::clearResponse();

		//This message tells FLEx that the upload dialog can now be closed.
		if ($cleared) {

			// If successfully cleared, send the success message and close the connection.
			Webonary_Utility::sendAndContinue(function() {
				echo "Upload successful. An email will be sent to you when processing is complete.\n";
			});
		}
		else {

			// If not successfully cleared, send the success message and flush to the client.
			// The connection will remain open.
			echo "Upload successful. Beginning processing....\n";
			flush();
		}

		//include $webonary_include_path . DS . 'run_import.php';
		$import = new Webonary_Pathway_Xhtml_Import();

		$import->api = true;
		$import->verbose = false;

		$import->process_xhtml_file($fileConfigured, 'configured', $user);
	}
}
