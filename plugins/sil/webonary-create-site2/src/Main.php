<?php

namespace SIL\WebonaryCreateSite2;

class Main
{
	public static function Run(): int
	{
		return Hooks::SetHooks();
	}
}
