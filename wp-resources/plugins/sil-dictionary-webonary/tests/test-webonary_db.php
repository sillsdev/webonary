<?php

class Webonary_Db_Test extends WP_UnitTestCase
{
	function test_GetBool()
	{
		$sql = 'SELECT COUNT(*) FROM wptests_options WHERE option_name = %s OR option_name = %s';
		$result = Webonary_Db::GetBool($sql, 'title 1', 'title 2');
		$this->assertTrue($result === false);

		$result = Webonary_Db::GetBool($sql, 'home', 'title 2');
		$this->assertTrue($result === true);
	}
}
