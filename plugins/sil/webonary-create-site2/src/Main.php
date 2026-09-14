<?php

namespace SIL\WebonaryCreateSite2;

class Main
{
	public static string $text_domain = 'webonary-create-site2';

	public static function Run(): int
	{
		return Hooks::SetHooks();
	}
}
