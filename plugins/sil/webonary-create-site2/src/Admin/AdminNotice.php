<?php

namespace SIL\WebonaryCreateSite2\Admin;

use SIL\WebonaryCreateSite2\Abstracts\NoticeType;

class AdminNotice
{
	private string $type;
	private string $msg;

	/**
	 * @param NoticeType $type
	 * @param string $msg
	 */
	public function __construct(NoticeType $type, string $msg)
	{
		$this->type = $type->value;
		$this->msg = $msg;
		add_action('copier_notices', [$this, 'Render']);
	}

	public function Render(): void
	{
		echo <<<HTML
<div class="notice notice-$this->type is-dismissible">
    <p>$this->msg</p>
</div>
HTML;
	}
}
