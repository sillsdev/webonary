<?php

class Webonary_Cache
{
	private static ?string $cache_directory = null;
	private static int $cache_duration = 7200; // one day = 86400 seconds, one hour = 3600 seconds

	public static function GetCacheDir(string $site_name): ?string
	{
		if (is_null(self::$cache_directory) || !is_dir(self::$cache_directory)) {

			$dir = rtrim(sys_get_temp_dir(), '/\\') . '/webonary-cache';

			if (!is_dir($dir))
				mkdir($dir, 0775, true);

			self::$cache_directory = $dir;
		}

		$site_dir = self::$cache_directory . '/' . $site_name;
		if (!is_dir($site_dir))
			mkdir($site_dir, 0775, true);

		return $site_dir;
	}

	public static function Save(string $option_name, string $site_name, mixed $data): void
	{
		$key = self::GetCacheDir($site_name) . DS . $option_name . '.cache';
		$serialized = maybe_serialize($data);
		file_put_contents($key, gzcompress($serialized));
	}

	/**
	 * @param string $option_name
	 * @param string $site_name
	 * @return mixed
	 */
	public static function Get(string $option_name, string $site_name): mixed
	{
		$key = self::GetCacheDir($site_name) . DS . $option_name . '.cache';

		// if the file does not exist, return null
		if (!is_file($key))
			return null;

		// check if expired
		$file_created = filemtime($key);
		if (($file_created + self::$cache_duration) < time()) {
			unlink($key);
			return null;
		}

		$serialized = gzuncompress(file_get_contents($key));
		return maybe_unserialize($serialized);
	}

	public static function Delete(string $option_name, string $site_name): void
	{
		$key = self::GetCacheDir($site_name) . DS . $option_name . '.cache';

		if (is_file($key))
			unlink($key);
	}

	public static function DeleteAllForDictionary(string $site_name): void
	{
		self::ClearDirectory(self::GetCacheDir($site_name), false);
	}

	private static function ClearDirectory(string $dirName, bool $deleteDirectoryAlso): void
	{
		$structure = glob(rtrim($dirName, "/") . '/*');

		if (is_array($structure)) {
			foreach ($structure as $file) {

				if (is_dir($file))
					self::ClearDirectory($file, true);
				elseif (is_file($file))
					unlink($file);
			}
		}

		if ($deleteDirectoryAlso)
			rmdir($dirName);
	}
}
