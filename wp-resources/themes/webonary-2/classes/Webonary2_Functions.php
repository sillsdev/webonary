<?php

class Webonary2_Functions
{
	private static $is_test_server = null;
	private static $default_css = null;

	public static function IsTestServer(): ?bool {

		if (is_null(self::$is_test_server)) {
			$host = (string)filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_UNSAFE_RAW);
			self::$is_test_server = ((strpos($host, 'localhost') !== false) || strpos($host, 'webonary.work') !== false);
		}

		return self::$is_test_server;
	}

	public static function DefaultCSS(): string {

		if (is_null(self::$is_test_server)) {
			if (Webonary2_Functions::IsTestServer())
				self::$default_css = '/wp-content/themes/webonary-2/js/style.css';
			else
				self::$default_css = '/wp-content/themes/webonary-2/js/style.min.css';
		}

		return self::$default_css;
	}

	public static function SiteLogo(): string {

		$logo = esc_url(get_theme_mod('webonary_logo') ?? '');
		$home_url = home_url();

		if (!empty($logo))
			return "<a href=\"$home_url\" class=\"navbar-brand\"><img src=\"$logo\" alt=\"Logo\"></a>";

		return '';
	}

	public static function PageTitle(): string {

		$title = get_bloginfo('name', 'display');
		if(is_home() || is_front_page())
			return $title . ' - ' . get_bloginfo('description', 'display');
		else
			return $title . wp_title('&raquo;', false);
	}



}
