<?php

namespace SIL\Webonary;

use SIL\Webonary\Helpers\ShortCodes;

class Main
{
	public static function Run(): void
	{
		Hooks::SetHooks();
		ShortCodes::Init();

		if (is_admin())
			Admin::ApplyAdminSettings();
	}
}
