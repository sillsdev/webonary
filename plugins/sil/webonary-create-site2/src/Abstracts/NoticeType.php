<?php

namespace SIL\WebonaryCreateSite2\Abstracts;

enum NoticeType: string
{
	case Success = 'success';
	case Warning = 'warning';
	case Error = 'error';
	case Info = 'info';
}
