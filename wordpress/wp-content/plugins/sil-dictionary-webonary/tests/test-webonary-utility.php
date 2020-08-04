<?php


class Webonary_Utility_Test extends WP_UnitTestCase
{
	function test_CalculateRectangle()
	{
		$rect = Webonary_Utility::CalculateRectangle(1024, 600, 150, 150);
		$this->assertNotEmpty($rect);
		$this->assertLessThanOrEqual(150, $rect->width);
		$this->assertLessThanOrEqual(150, $rect->height);

		// check the aspect ratio
		$expected = 1024/600;
		$actual = $rect->width/$rect->height;
		$this->assertEquals($expected, $actual, '', 0.8);

		$rect = Webonary_Utility::CalculateRectangle(1024, 100, 150, 150);
		$this->assertNotEmpty($rect);
		$this->assertLessThanOrEqual(150, $rect->width);
		$this->assertLessThanOrEqual(150, $rect->height);

		// check the aspect ratio
		$expected = 1024/100;
		$actual = $rect->width/$rect->height;
		$this->assertEquals($expected, $actual, '', 0.8);
	}

	function test_ResizeThisImage()
	{
		$dst_dir = sys_get_temp_dir() . '/test_ResizeThisImage';
		if (is_dir($dst_dir))
			Webonary_Utility::recursiveRemoveDir($dst_dir);

		mkdir($dst_dir, 0777, true);

		$src = __DIR__ . '/resources/img_400x640.jpg';
		$result = Webonary_Utility::ResizeThisImage(IMAGETYPE_JPEG, 400, 640, 150, 150, $src, $dst_dir);
		$this->assertTrue($result);

		$src = __DIR__ . '/resources/img_640x400.gif';
		$result = Webonary_Utility::ResizeThisImage(IMAGETYPE_GIF, 640, 400, 150, 150, $src, $dst_dir);
		$this->assertTrue($result);

		$src = __DIR__ . '/resources/img_640x400.png';
		$result = Webonary_Utility::ResizeThisImage(IMAGETYPE_PNG, 640, 400, 150, 150, $src, $dst_dir);
		$this->assertTrue($result);

		if (is_dir($dst_dir))
			Webonary_Utility::recursiveRemoveDir($dst_dir);
	}

	function test_ResizeThisImageBadImage()
	{
		$dst_dir = sys_get_temp_dir() . '/test_ResizeThisImageBadImage';
		if (is_dir($dst_dir))
			Webonary_Utility::recursiveRemoveDir($dst_dir);

		mkdir($dst_dir, 0777, true);

		$src = __DIR__ . '/resources/not-an-image.png';

		// we are expecting imagecreatefrompng() to throw an error in Webonary_Utility::ResizeThisImage
		$this->expectException('PHPUnit\Framework\Error\Error');

		Webonary_Utility::ResizeThisImage(IMAGETYPE_PNG, 640, 400, 150, 150, $src, $dst_dir);

		if (is_dir($dst_dir))
			Webonary_Utility::recursiveRemoveDir($dst_dir);
	}
}
