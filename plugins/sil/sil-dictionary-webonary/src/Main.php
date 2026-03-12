<?php

namespace SIL\Webonary;

class Main
{
	public static function Run(): void
	{
		Hooks::SetHooks();

		if (is_admin())
			Admin::ApplyAdminSettings();
	}
}
