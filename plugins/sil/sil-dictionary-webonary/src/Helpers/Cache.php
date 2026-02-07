<?php

namespace SIL\Webonary\Helpers;

class Cache
{
	private static ?string $cache_directory = null;
	private static int $cache_duration = 7200; // one day = 86400 seconds, one hour = 3600 seconds

	/**
	 * Gets the path to the cache directory for this site.
	 *
	 * @return string The directory path.
	 */
	public static function GetCacheDir(): string
	{
		if (is_null(self::$cache_directory))
			self::$cache_directory = rtrim(sys_get_temp_dir(), '/\\') . '/webonary-cache/site-' . get_current_blog_id();

		if (!is_dir(self::$cache_directory))
			mkdir(self::$cache_directory, 0775, true);

		return self::$cache_directory;
	}

	/**
	 * Gets the cache key (file name) for this option.
	 *
	 * @param string $option_name
	 * @return string The file path.
	 */
	private static function GetCacheKey(string $option_name): string
	{
		return self::GetCacheDir() . DS . $option_name . '.cache';
	}

	/**
	 * Saves data to the cache for this site.
	 *
	 * @param string $option_name
	 * @param mixed $data
	 * @return void
	 */
	public static function Save(string $option_name, mixed $data): void
	{
		$serialized = maybe_serialize($data);
		file_put_contents(self::GetCacheKey($option_name), gzcompress($serialized));
	}

	/**
	 * Retrieves data from the cache for this site.
	 *
	 * @param string $option_name
	 * @return mixed The data, or NULL if not found or expired.
	 */
	public static function Get(string $option_name): mixed
	{
		$key = self::GetCacheKey($option_name);

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

	/**
	 * Deletes the cache entry, if it exists.
	 *
	 * @param string $option_name
	 * @return void
	 */
	public static function Delete(string $option_name): void
	{
		$key = self::GetCacheKey($option_name);

		if (is_file($key))
			unlink($key);
	}

	/**
	 * Deletes all cache entries for the current dictionary.
	 *
	 * @return void
	 */
	public static function DeleteAllForThisDictionary(): void
	{
		self::ClearDirectory(self::GetCacheDir(), false);
	}

	/**
	 * Deletes all cache entries for all dictionaries.
	 *
	 * @return void
	 */
	public static function DeleteAllForAllDictionaries(): void
	{
		self::ClearDirectory(dirname(self::GetCacheDir()), false);
	}

	/**
	 * Remove all contents of the directory recursively. Optionally delete the directory also.
	 *
	 * @param string $dir_name
	 * @param bool $delete_directory_also
	 * @return void
	 */
	private static function ClearDirectory(string $dir_name, bool $delete_directory_also): void
	{
		$structure = glob(rtrim($dir_name, "/") . '/*');

		if (is_array($structure)) {
			foreach ($structure as $file) {

				if (is_dir($file))
					self::ClearDirectory($file, true);
				elseif (is_file($file))
					unlink($file);
			}
		}

		if ($delete_directory_also)
			rmdir($dir_name);
	}
}
