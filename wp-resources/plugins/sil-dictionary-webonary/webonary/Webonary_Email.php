<?php

class Webonary_Email
{
	/**
	 * Customize the text for confirmation email sent when the "Administration Email Address" for a site is changed
	 *
	 * @param string $template
	 * @return string
	 */
	public static function AdminEmailChangeHandler(string $template): string
	{
		return <<<TXT
Howdy ###USERNAME###,

Someone with administrator capabilities recently requested to have the
administration email address changed on this site:
###SITEURL###

To confirm this change, please click on the following link:
###ADMIN_URL###

You can safely ignore and delete this email if you do not want to
take this action.

This email has been sent to ###EMAIL###

Regards,
All at ###SITENAME###
###SITEURL###
TXT;
	}
}
