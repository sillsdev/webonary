<?php
/** @noinspection PhpUnused */
/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection PhpMissingReturnTypeInspection */
/** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection SqlResolve */

class Webonary_Utility
{
	// Legacy default
	private static int $default_posts_per_page = 25;

	private static int $posts_per_page = 0;
	private static int $current_page_number = 0;
	private static ?IntlDateFormatter $date_formatter;

	// Receive upload. Unzip it to uploadPath. Remove upload file.
	public static function unzip($zip_file, $uploadPath, $zipFolderPath)
	{
		if (!function_exists('wp_handle_upload'))
		{
			require_once( ABSPATH . '/wp-admin/includes/file.php' );
		}

		$overrides = array( 'test_form' => false, 'test_type' => false );
		$file = wp_handle_upload($zip_file, $overrides);

		if (isset( $file['error']))
		{
			error_log('Error: Upload failed: ' . $file['error']);
			unlink($uploadPath . '/' . $zip_file['name']);
			return false;
		}

		if(file_exists(WP_CONTENT_DIR . "/archives"))
		{
			copy($uploadPath . '/' . $_FILES['file']['name'], WP_CONTENT_DIR . "/archives/" . $_FILES['file']['name']);
		}

		$zip = new ZipArchive;
		$res = $zip->open($uploadPath . '/' . $zip_file['name']);
		if ($res === FALSE)
		{
			error_log('Error: ' . $zip_file['name'] . ' is not a valid zip file');
			unlink($uploadPath . '/' . $zip_file['name']);
			return false;
		}

		$unzip_success = $zip->extractTo($zipFolderPath);
		$zip->close();
		if(!$unzip_success)
		{
			error_log('Error: could not extract zip file to ' . $uploadPath);
			unlink($uploadPath . '/' . $zip_file['name']);
			return false;
		}

		unlink($uploadPath . '/' . $zip_file['name']);
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
					$messages[] = "Ignoring \"$entry\": file is not a valid image file.";
					continue;
				}

				// information returned by getimagesize
				$width = $image_info[0];
				$height = $image_info[1];
				$img_type = $image_info[2];

				// make sure the image is an allowed type
				if (!in_array($img_type, $allowed_types)) {
					$messages[] = "Ignoring \"$entry\": file is not an allowed image type.";
					continue;
				}

				// skip zero-width images
				if ($width == 0) {
					$messages[] = "Ignoring \"$entry\": image has a width of zero.";
					continue;
				}

				// skip zero-height images
				if ($height == 0) {
					$messages[] = "Ignoring \"$entry\": image has a height of zero.";
					continue;
				}

				if ($h < $height || $w < $width)
				{
					// NB: the image needs to be resized into a thumbnail

					$result = self::ResizeThisImage($img_type, $width, $height, $w, $h, $file_name, $dst_dir);

					if ($result === false)
						$messages[] = "Warning: not able to save thumbnail \"$entry\".";
				}
				else
				{
					// NB: the image is already small enough to be a thumbnail, just copy to the output directory

					$result = copy($src_dir . '/' . $entry, $dst_dir . '/' . $entry);

					if ($result === false)
						$messages[] = "Warning: not able to copy thumbnail \"$entry\".";
				}
			}
			catch(Exception $e) {
				$err_msg = $e->getMessage();
				$messages[] = "Error resizing image \"$entry\": " . $err_msg;
				error_log("Error: There was an error converting image file \"$entry\" to thumbnail: " . $err_msg);
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

		return match ($img_type) {
			IMAGETYPE_PNG => imagepng($dst_image, $dst_dir . '/' . $entry),
			IMAGETYPE_GIF => imagegif($dst_image, $dst_dir . '/' . $entry),
			IMAGETYPE_JPEG => imagejpeg($dst_image, $dst_dir . '/' . $entry, 90),
			default => false,
		};
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

	public static function includeTemplate($template_name, $substitutions = null)
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
		$re  = '/\/page\/(\d+)/';
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

	public static function escapeSql($string): string
	{
		return addcslashes($string, "\0..\37\"'\\");
	}

	public static function escapeSqlLike($string): string
	{
		return addcslashes($string, "\0..\37\"'_%\\");
	}

	/**
	 * Utility function to convert pseudo-links in entry xml from FLex into actual Webonary site links
	 * @param string $entry_xml
	 * @return string $entry_xml
	 */
	public static function fix_entry_xml_links($entry_xml)
	{
		//this replaces a link like this: <a href="#gcec78a67-91e9-4e72-82d3-4be7b316b268">
		//to this: <a href="/gcec78a67-91e9-4e72-82d3-4be7b316b268">
		//but it will keep a link like this: <a href="#gcec78a67-91e9-4e72-82d3-4be7b316b268" onclick="document.getElementById('g635754005092954976ã').play()"
		//which is important for playing audio

		//first make sure audio href only contains a hashtag (or any href with onclick after it)
		$entry_xml = preg_replace('/href="(#)([^"]+)" onclick/', 'href="#$2" onclick', $entry_xml);

		//closing tag for <a .play()"/>, needs to have an empty space between > </a>
		$entry_xml = str_replace('></a>', '> </a>', $entry_xml);

		//make all links that are not using onclick (e.g. have format "#">) use the url path
		$entry_xml = preg_replace('/href="(#)([^"]+)">/', 'href="' . get_bloginfo('wpurl') . '/\\2">', $entry_xml);

		$entry_xml = addslashes($entry_xml);
		$entry_xml = stripslashes($entry_xml);

		/** @noinspection PhpMultipleClassDeclarationsInspection */
		return normalizer_normalize($entry_xml, Normalizer::NFC );
	}

	/** @noinspection PhpUnused */
	public static function EnqueueJsAndCss()
	{
		if (!is_admin()) {
			wp_register_script('webonary_plugin_script', plugin_dir_url(__DIR__) . 'js/webonary.js', [], false, true);
			wp_enqueue_script('webonary_plugin_script');
		}

		wp_register_style(
			'webonary_dictionary_style',
			plugin_dir_url(__DIR__) . 'css/dictionary_styles.css',
			[],
			date('U')
		);
		wp_enqueue_style('webonary_dictionary_style');

		// <link rel="stylesheet" href="<?php echo get_bloginfo('wpurl'); // >/wp-content/plugins/wp-page-numbers/classic/wp-page-numbers.css" />

		if (is_page())
			return;

		if (IS_CLOUD_BACKEND)
		{
			Webonary_Cloud::registerAndEnqueueMainStyles(['webonary_dictionary_style']);
		}
		else
		{
			$upload_dir = wp_upload_dir();
			wp_register_style(
				'configured_stylesheet',
				$upload_dir['baseurl'] . '/imported-with-xhtml.css',
				['webonary_dictionary_style'],
				date('U')
			);
			wp_enqueue_style('configured_stylesheet');

			$overrides_css = $upload_dir['basedir'] . '/ProjectDictionaryOverrides.css';
			if (!file_exists($overrides_css))
				$overrides_css = $upload_dir['baseurl'] . '/ProjectDictionaryOverrides.css';

			if (file_exists($overrides_css))
			{
				wp_register_style(
					'overrides_stylesheet',
					$overrides_css,
					['webonary_dictionary_style', 'configured_stylesheet'],
					date('U')
				);
				wp_enqueue_style('overrides_stylesheet');
			}
		}
	}

	/**
	 * Loads the translated strings
	 */
	public static function LoadTextDomains()
	{
		$include_dir = 'sil-dictionary-webonary/include';
		load_plugin_textdomain('sil_dictionary', false, $include_dir . '/lang');
		load_plugin_textdomain('sil_domains', false, $include_dir . '/sem-domains');
	}

	/**
	 * Returns the $_GET[$variable_name] value as a string
	 * @param string $variable_name
	 * @param string $default
	 * @return string
	 */
	public static function GetStr($variable_name, $default = '')
	{
		$val = filter_input(INPUT_GET, $variable_name, FILTER_UNSAFE_RAW) ?? $default;
		return ($val === false) ? $default : (string)$val;
	}

	/**
	 * Returns the $_GET[$variable_name] value as a float
	 * @param string $variable_name
	 * @param float $default
	 * @param int|null $decimal_places
	 * @return float
	 */
	public static function GetFloat($variable_name, $default = 0.0, $decimal_places = null)
	{
		$val = filter_input(INPUT_GET, $variable_name, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?? $default;

		if ($val === false || $val === '')
			$val = $default;

		if ($decimal_places != null) $val = number_format($val, $decimal_places);

		return (float) $val;
	}

	/**
	 * Returns the $_GET[$variable_name] value as an int
	 * @param string $variable_name
	 * @param int $default
	 * @return int
	 */
	public static function GetInt($variable_name, $default = 0)
	{
		$val = self::GetFloat($variable_name, $default, 0);
		return (int)$val;
	}

	public static function GetLongDateFormatter()
	{
		if (!empty(self::$date_formatter))
			return self::$date_formatter;

		self::$date_formatter = new IntlDateFormatter(
			get_locale(),
			IntlDateFormatter::LONG,
			IntlDateFormatter::NONE,
			IntlTimeZone::getGMT(),
			IntlDateFormatter::GREGORIAN
		);

		return self::$date_formatter;
	}

	public static function FormatLongDate($timestamp)
	{
		// first, see if there is a custom format defined in qTranslate
		if (function_exists('qtranxf_get_language_date_or_time_format')) {

			$test = qtranxf_get_language_date_or_time_format('date_format');
			if (!empty($test) && !str_contains($test, '%'))
				$pattern = $test;
		}

		// if not, get the pattern from the current locale
		if (empty($pattern)) {

			$pattern = self::GetLongDateFormatter()->getPattern();

			// convert the pattern from ICU format to PHP format, which is used by the wp_date() function
			// https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table
			// https://www.php.net/manual/en/datetime.format.php
			$pattern = preg_replace('/[yY]+/', 'Y', $pattern);
			$pattern = preg_replace('/(^|[^M])M($|[^M])/', '\\1n\\2', $pattern);
			$pattern = preg_replace('/(^|[^M])MM($|[^M])/', '\\1m\\2', $pattern);
			$pattern = preg_replace('/(^|[^M])MMM($|[^M])/', '\\1M\\2', $pattern);
			$pattern = preg_replace('/(^|[^M])MMMM($|[^M])/', '\\1F\\2', $pattern);
			$pattern = preg_replace('/(^|[^L])L($|[^L])/', '\\1n\\2', $pattern);
			$pattern = preg_replace('/(^|[^L])LL($|[^L])/', '\\1m\\2', $pattern);
			$pattern = preg_replace('/(^|[^L])LLL($|[^L])/', '\\1M\\2', $pattern);
			$pattern = preg_replace('/(^|[^L])LLLL($|[^L])/', '\\1F\\2', $pattern);
			$pattern = preg_replace('/(^|[^E])E{1,3}($|[^E])/i', '\\1D\\2', $pattern);
			$pattern = preg_replace('/(^|[^E])EEEE($|[^E])/i', '\\1l\\2', $pattern);
			$pattern = preg_replace('/(^|[^c])c{1,3}($|[^c])/', '\\1D\\2', $pattern);
			$pattern = preg_replace('/(^|[^c])cccc($|[^c])/', '\\1l\\2', $pattern);
			$pattern = preg_replace('/(^|[^d])d($|[^d])/', '\\1j\\2', $pattern);
			$pattern = preg_replace('/(^|[^d])dd($|[^d])/', '\\1d\\2', $pattern);
			$pattern = preg_replace('/(^|[^D])D($|[^D])/', '\\1z\\2', $pattern);
			$pattern = preg_replace('/(^|[^G])G{1,5}($|[^G])/', '\\1\\2', $pattern);
		}

		// use wp_date() to get the localized month and day names from `wordpress/wp-content/languages/*.mo`
		return wp_date($pattern, $timestamp);
	}

	public static function RemoveEmptyStrings($array): array
	{
		return array_values(array_filter($array, function($val) { return strlen(trim($val)) > 0; }));
	}

	/**
	 * Removes whitespace and separators from both ends of a string.
	 *  - \p{Z} = any whitespace or invisible separator
	 *  - \x{200B} = zero-width space
	 *  - \x{200C} = zero-width non-joiner
	 *  - \x{200D} = zero-width joiner
	 *  - \x{2060} = word joiner
	 *
	 * @param string $string
	 * @return string
	 */
	public static function UnicodeTrim(string $string): string
	{
		return preg_replace('/(^[\p{Z}\x{200B}-\x{200D}\x{2060}]+)|([\p{Z}\x{200B}-\x{200D}\x{2060}]+$)/u', '', $string);
	}
}
