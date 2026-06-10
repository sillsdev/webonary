<?php

namespace SIL\WebonaryCreateSite2\Admin;

class AdminNotice
{
	private string $type;
	private string $msg;

	/**
	 * @param string $type Values: "success", "warning", "error, "info"
	 * @param string $msg
	 */
	public function __construct(string $type, string $msg)
	{
		$this->type = $type;
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
