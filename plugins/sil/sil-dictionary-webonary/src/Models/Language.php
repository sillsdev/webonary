<?php

namespace SIL\Webonary\Models;

use SIL\Webonary\Interfaces\ILanguage;

class Language implements ILanguage
{
	public string $Code;
	public string $Name;
	public int $TotalIndexed;
	public bool $IsMain;
	public bool $IsReversal;
	public bool $Hidden = false;

	/**
	 * @param string $code
	 * @param string $name
	 * @param int $total_indexed
	 * @param bool $is_main
	 * @param bool $is_reversal
	 */
	public function __construct(string $code, string $name, int $total_indexed = 0, bool $is_main = false, bool $is_reversal = false)
	{
		$this->Code = $code;
		$this->Name = $name;
		$this->TotalIndexed = $total_indexed;
		$this->IsMain = $is_main;
		$this->IsReversal = $is_reversal;
	}

	public function __serialize(): array
	{
		return [
			'lang_code' => $this->Code,
			'lang_name' => $this->Name,
			'total_indexed' => $this->TotalIndexed,
			'is_main' => $this->IsMain,
			'is_reversal' => $this->IsReversal,
			'is_hidden' => $this->Hidden
		];
	}

	public function __unserialize(array $data): void
	{
		$this->Code = $data['lang_code'];
		$this->Name = $data['lang_name'];
		$this->TotalIndexed = $data['total_indexed'];
		$this->IsMain = $data['is_main'];
		$this->IsReversal = $data['is_reversal'];
		$this->Hidden = $data['is_hidden'];
	}
}
