<?php
/** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection SqlResolve */

class Webonary_Utility
{
	// Legacy default
	private static $default_posts_per_page = 25;

	private static $posts_per_page = 0;
	private static $current_page_number = 0;

	// Receive upload. Unzip it to uploadPath. Remove upload file.
	public static function unzip($zipfile, $uploadPath, $zipFolderPath)
	{
		if (!function_exists('wp_handle_upload'))
		{
			require_once( ABSPATH . '/wp-admin/includes/file.php' );
		}

		$overrides = array( 'test_form' => false, 'test_type' => false );
		$file = wp_handle_upload($zipfile, $overrides);

		if (isset( $file['error']))
		{
			error_log('Error: Upload failed: ' . $file['error']);
			unlink($uploadPath . '/' . $zipfile['name']);
			return false;
		}

		if(file_exists(WP_CONTENT_DIR . "/archives"))
		{
			copy($uploadPath . '/' . $_FILES['file']['name'], WP_CONTENT_DIR . "/archives/" . $_FILES['file']['name']);
		}

		$zip = new ZipArchive;
		$res = $zip->open($uploadPath . '/' . $zipfile['name']);
		if ($res === FALSE)
		{
			error_log('Error: ' . $zipfile['name'] . ' is not a valid zip file');
			unlink($uploadPath . '/' . $zipfile['name']);
			return false;
		}

		$unzip_success = $zip->extractTo($zipFolderPath);
		$zip->close();
		if(!$unzip_success)
		{
			error_log('Error: could not extract zip file to ' . $uploadPath);
			unlink($uploadPath . '/' . $zipfile['name']);
			return false;
		}

		unlink($uploadPath . '/' . $zipfile['name']);
		return true;
	}

	// Function to remove folders and files
	public static function recursiveRemoveDir($dir)
	{
		if (is_dir($dir)) {
			$files = scandir($dir);
			foreach ($files as $file)
			{
				if ($file != "." && $file != "..") self::recursiveRemoveDir("$dir/$file");
				self::deleteDirectory($dir);
			}
		}
		else if (file_exists($dir)) unlink($dir);
	}

	private static function deleteDirectory($dir)
	{
		if (!file_exists($dir)) {
			return true;
		}

		if (!is_dir($dir)) {
			return unlink($dir);
		}

		foreach (scandir($dir) as $item) {
			if ($item == '.' || $item == '..') {
				continue;
			}

			if (!self::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
				return false;
			}

		}

		return rmdir($dir);
	}

	// Function to Copy folders and files
	public static function recursiveCopy($src, $dst)
	{
		if (is_dir ( $src ))
		{
			if(!file_exists($dst))
				mkdir ( $dst, 0777, true );

			$files = scandir ( $src );
			foreach ( $files as $file )
				if ($file != "." && $file != "..")
					self::recursiveCopy ( "$src/$file", "$dst/$file" );
		}
		elseif (file_exists ( $src ))
			copy ( $src, $dst );

	}

	/**
	 * Resize all of the allowed images files in the source directory and save them to the destination directory
	 *
	 * @param string $src_dir
	 * @param int $w
	 * @param int $h
	 * @param string $dst_dir
	 *
	 * @return string[]
	 */
	public static function resizeImages($src_dir, $w, $h, $dst_dir)
	{
		/** A list of error and warning messages returned by the function */
		$messages = [];

		/** A list of the accepted image file types. All others will be ignored. */
		$allowed_types = [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF];

		/** A list of the files and directories in $src_dir */
		$entries = [];

		try {
			// create the destination directory
			if(!file_exists($dst_dir))
				mkdir($dst_dir);

			// get the list of files to process
			$entries = scandir ($src_dir);
		}
		catch(Exception $e) {
			$err_msg = $e->getMessage();
			$messages[] = "Error initializing resizeImages: " . $err_msg;
			error_log('Error initializing resizeImages: ' . $err_msg);
		}

		// process each file found
		foreach ($entries as $entry)
		{
			try {
				// ignore this directory and the parent
				if ($entry == '.' || $entry == '..')
					continue;

				$file_name = $src_dir . '/' . $entry;

				// if the entry isn't a file, continue
				if (!is_file($file_name))
					continue;

				// get the image info [width, height, type]
				$image_info = getimagesize($file_name);

				// will be false if getimagesize fails
				if ($image_info === false) {
					$messages[] = "Ignoring \"{$entry}\": file is not a valid image file.";
					continue;
				}

				// information returned by getimagesize
				$width = $image_info[0];
				$height = $image_info[1];
				$img_type = $image_info[2];

				// make sure the image is an allowed type
				if (!in_array($img_type, $allowed_types)) {
					$messages[] = "Ignoring \"{$entry}\": file is not an allowed image type.";
					continue;
				}

				// skip zero-width images
				if ($width == 0) {
					$messages[] = "Ignoring \"{$entry}\": image has a width of zero.";
					continue;
				}

				// skip zero-height images
				if ($height == 0) {
					$messages[] = "Ignoring \"{$entry}\": image has a height of zero.";
					continue;
				}

				if ($h < $height || $w < $width)
				{
					// NB: the image needs to be resized into a thumbnail

					$result = self::ResizeThisImage($img_type, $width, $height, $w, $h, $file_name, $dst_dir);

					if ($result === false)
						$messages[] = "Warning: not able to save thumbnail \"{$entry}\".";
				}
				else
				{
					// NB: the image is already small enough to be a thumbnail, just copy to the output directory

					$result = copy($src_dir . '/' . $entry, $dst_dir . '/' . $entry);

					if ($result === false)
						$messages[] = "Warning: not able to copy thumbnail \"{$entry}\".";
				}
			}
			catch(Exception $e) {
				$err_msg = $e->getMessage();
				$messages[] = "Error resizing image \"{$entry}\": " . $err_msg;
				error_log("Error: There was an error converting image file \"{$entry}\" to thumbnail: " . $err_msg);
			}
		}

		return $messages;
	}

	/**
	 * Resizes this particular image file
	 *
	 * @param $img_type
	 * @param $src_width
	 * @param $src_height
	 * @param $max_width
	 * @param $max_height
	 * @param $src_file_name
	 * @param $dst_dir
	 *
	 * @return bool
	 */
	public static function ResizeThisImage($img_type, $src_width, $src_height, $max_width, $max_height, $src_file_name, $dst_dir)
	{
		// calculate new dimensions - make sure both dimensions are inside the [$w, $h] rectangle
		$rect = self::CalculateRectangle($src_width, $src_height, $max_width, $max_height);
		$entry = basename($src_file_name);

		$src_image = null;

		switch ($img_type) {
			case IMAGETYPE_PNG:
				$src_image = imagecreatefrompng($src_file_name);
				break;

			case IMAGETYPE_GIF:
				$src_image = imagecreatefromgif($src_file_name);
				break;

			case IMAGETYPE_JPEG:
				$src_image = imagecreatefromjpeg($src_file_name);
				break;

			default:
				return true;
		}

		$dst_image = imagecreatetruecolor($rect->width, $rect->height);
		imagecopyresized($dst_image, $src_image, $rect->x, $rect->y, 0, 0, $rect->width, $rect->height, $src_width, $src_height);

		switch ($img_type) {
			case IMAGETYPE_PNG:
				return imagepng($dst_image, $dst_dir . '/' . $entry);

			case IMAGETYPE_GIF:
				return imagegif($dst_image, $dst_dir . '/' . $entry);

			case IMAGETYPE_JPEG:
				return imagejpeg($dst_image, $dst_dir . '/' . $entry, 90);

			default:
				return false;
		}
	}

	/**
	 * Get the bounding rectangle for the new image, with the same aspect ratio.
	 *
	 * The returned rectangle will have the same aspect ratio as the source, and
	 * fit completely inside the max rectangle.
	 *
	 * @param int $source_width
	 * @param int $source_height
	 * @param int $max_width
	 * @param int $max_height
	 * @return stdClass
	 */
	public static function CalculateRectangle($source_width, $source_height, $max_width, $max_height)
	{
		$rect = new stdClass();

		// get an initial rectangle
		$rect->width = intval($max_width);
		$rect->height = intval($max_width * $source_height / $source_width);

		// check for out-of-bounds
		if ($rect->height > $max_height) {
			$rect->height = intval($max_height);
			$rect->width = intval($max_height * $source_width / $source_height);
		}

		// set the origin
		$rect->x = 0;
		$rect->y = 0;

		return $rect;
	}

	public static function verifyAdminPrivileges($username, $password)
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
			$user_role = unserialize($roleSerialized);

			if(!empty($user_role['editor']) || !empty($user_role['administrator']))
			{
				return get_userdata($user->ID);
			}
			else
			{
				echo "User doesn't have permission to import data to this Webonary site\n";
				return false;
			}

		}
		else
		{
			echo "Wrong username or password\n";
			return false;
		}
	}

	public static function includeTemplate($template_name, $substitutions)
	{
		global $webonary_template_path;

		$html = file_get_contents($webonary_template_path . DIRECTORY_SEPARATOR . $template_name);

		if (empty($substitutions))
			return $html;

		foreach($substitutions as $key => $value) {
			$html = str_replace($key, $value, $html);
		}

		return $html;
	}

	/**
	 * Executes $function, sends the response to the browser, closes the connection, and continues processing.
	 * @param Closure $function
	 */
	public static function sendAndContinue($function)
	{
		ignore_user_abort(true);
		set_time_limit(0);

		ob_start();

		$function();

		// set the response code to 200, if not already set

		if (http_response_code() === false)
			http_response_code(200);

		header('Connection: close');
		header('Content-Length: ' . ob_get_length());

		if (function_exists('fastcgi_finish_request')) {
			fastcgi_finish_request();
		}
		else{
			ob_end_flush();

			if(ob_get_level() > 0)
				ob_flush();

			flush();
		}
	}

	/**
	 * This function attempts to remove everything from the output buffers.
	 * Returns FALSE if the headers have already been sent.
	 *
	 * @return bool
	 */
	public static function clearResponse()
	{
		$headers_sent = headers_sent();

		// attempt to remove all headers
		if (!$headers_sent)
			header_remove();

		// discard all the buffered output
		while (ob_get_level()) {
			ob_end_clean();
		}

		return !$headers_sent;
	}

	public static function disablePlugins($plugins)
	{
		$key = array_search('qtranslate-x/qtranslate.php' , $plugins);
		if (false !== $key)
			unset($plugins[$key]);

		return $plugins;
	}

	/**
	 * Gets the number of posts per page
	 * @return int
	 */
	public static function getPostsPerPage()
	{
		if (!empty(self::$posts_per_page))
			return self::$posts_per_page;

		self::$posts_per_page = (int)get_option('posts_per_page', self::$default_posts_per_page);

		return self::$posts_per_page;
	}

	/**
	 * Gets the current page number
	 * @return int
	 */
	public static function getPageNumber()
	{
		if (!empty(self::$current_page_number))
			return self::$current_page_number;

		// first check for a page number in the URI
		$re  = '/(?:\/page\/)(\d+)/';
		$uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_UNSAFE_RAW, ['options' => ['default' => '']]);
		$matches = null;
		$found = preg_match($re, $uri, $matches);

		// if not found in the URL, look in the query string
		if (!empty($found))
			self::$current_page_number = (int)$matches[1];
		else
			self::$current_page_number = (int)filter_input(INPUT_GET, 'pagenr', FILTER_VALIDATE_INT, ['options' => ['default' => 1]]);

		if (self::$current_page_number < 1)
			self::$current_page_number = 1;

		return self::$current_page_number;
	}

	public static function setPageNumber($page_num)
	{
		self::$current_page_number = $page_num;

		if (self::$current_page_number < 1)
			self::$current_page_number = 1;
	}
}
