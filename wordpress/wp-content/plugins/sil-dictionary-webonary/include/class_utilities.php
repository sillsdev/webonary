<?php
class Webonary_Utility
{
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
			echo "Error: " . $zipfile['name'] . " isn't a valid zip file";
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
	public static function recursiveRemoveDir($dir)
	{
		if (is_dir($dir)) {
			$files = scandir($dir);
			foreach ($files as $file)
			{
				if ($file != "." && $file != "..") self::recursiveRemoveDir("$dir/$file");
				error_log("removed: " . $dir);
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
		if (is_dir ( $src )) {
			if(!file_exists($dst))
			{
				error_log("create folder: " . $dst);
				mkdir ( $dst, 0777, true );
			}
			$files = scandir ( $src );
			foreach ( $files as $file )
				if ($file != "." && $file != "..")
					self::recursiveCopy ( "$src/$file", "$dst/$file" );
		} else if (file_exists ( $src )) {
			copy ( $src, $dst );
			error_log("moved: " . $src . " to " . $dst);
		}
	}

	public static function resize_image($src, $w, $h, $dst)
	{
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

					try {
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
					catch(Exception $e) {
						echo 'There was an error converting image file to thumbnail: ' .$e->getMessage();
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
			$userrole = unserialize($roleSerialized);

			if($userrole['editor'] == true)
			{
				$user_info = get_userdata($user->ID);
				return $user_info;
			}
			else
			{
				echo "User doesn't have permission to import data to this Webonary site\n";
				return false;
			}

		}
		else
		{
			echo "Wrong username or password.";
			return false;
		}
	}
}