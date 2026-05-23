<?php

namespace SIL\Webonary\Helpers;

class GA4Helper
{
	private static string $ga4_id = '';
	private static bool $ga4_id_found = false;

	public static function SetGA4ID(string $ga4_id): void
	{
		self::$ga4_id = $ga4_id;
	}

	public static function HookMonsterInsightsG4ID($g4id): void
	{
		if (!empty(self::$ga4_id) && $g4id == self::$ga4_id)
			self::$ga4_id_found = true;
	}

	public static function HookHead(): string
	{
		if (self::$ga4_id_found || empty(self::$ga4_id))
			return '';

		$ga4_id = self::$ga4_id;

		$html = <<<HTML
<script async src="https://www.googletagmanager.com/gtag/js?id=$ga4_id&l=webonaryLayer"></script>
<script>
  window['webonaryLayer'] = [];
  window['webonaryLayer'].push('js', new Date());
  window['webonaryLayer'].push('config', '$ga4_id');
</script>
HTML;

		if (!defined('PHP_UNIT'))
			echo $html;

		return $html;
	}

	public static function ResetForTesting(): void
	{
		self::$ga4_id = '';
		self::$ga4_id_found = false;
	}
}
