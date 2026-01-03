<?php
/** @noinspection PhpMultipleClassDeclarationsInspection */

namespace SIL\Webonary\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Report
{
	public string $slug;
	public string $title;
	public string $show_in_list;

	public function __construct(string $slug, string $title, bool $show_in_list)
	{
		$this->slug = $slug;
		$this->title = $title;
		$this->show_in_list = $show_in_list;
	}
}
