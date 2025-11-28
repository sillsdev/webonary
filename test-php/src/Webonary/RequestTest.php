<?php

namespace SIL\Tests\Webonary;

use SIL\Tests\Mocks\MockRequest;
use WP_UnitTestCase;
use SIL\Webonary\Helpers\Request;

/**
 * @covers SIL\Webonary\Helpers\Request
 * @covers SIL\Tests\Mocks\MockRequest
 * @noinspection PhpUndefinedNamespaceInspection
 */
class RequestTest extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        MockRequest::Init();
    }

    public function testIsSet()
    {
        // server
        $this->assertFalse(Request::IsServerSet('TEST_VAL'));
        $_SERVER['TEST_VAL'] = 1;
        $this->assertTrue(Request::IsServerSet('TEST_VAL'));

        // post
        $this->assertFalse(Request::IsPostSet('TEST_VAL'));
        $_POST['TEST_VAL'] = 2;
        $this->assertTrue(Request::IsPostSet('TEST_VAL'));

        // get
        $this->assertFalse(Request::IsGetSet('TEST_VAL'));
        $_GET['TEST_VAL'] = 3;
        $this->assertTrue(Request::IsGetSet('TEST_VAL'));
    }

    public function testServer()
    {
        $_SERVER['testServer'] = 'test val';
        $this->assertEquals('test val', Request::ServerStr('testServer'));

        // test handling empty strings
        $this->assertEquals('', Request::ServerStr('testServer2'));

        $_SERVER['testServer2'] = '';
        $this->assertEquals('', Request::ServerStr('testServer2'));

        $_SERVER['testServer2'] = '    ';
        $this->assertEquals('', Request::ServerStr('testServer2'));
    }

    public function testPost()
    {
        $_POST['testPost_STR'] = 'test val';
        $_POST['testPost_NUM'] = 2.2;
        $_POST['testPost_TRUE'] = 1;
        $_POST['testPost_FALSE'] = 'false';
        $_POST['testPost_DATE'] = '2020-12-31 00:00:00+00:00';
        $this->assertSame('test val', Request::PostStr('testPost_STR'));
        $this->assertSame(2, Request::PostInt('testPost_NUM'));
        $this->assertSame(2.2, Request::PostFloat('testPost_NUM'));
        $this->assertSame(true, Request::PostBool('testPost_TRUE'));
        $this->assertSame(false, Request::PostBool('testPost_FALSE'));
        $this->assertSame(1609372800, Request::PostDate('testPost_DATE'));
		$this->assertNull(Request::PostDate('BOGUS'));

        $this->assertSame('test val', Request::PostGetStr('testPost_STR'));
        $this->assertSame(2, Request::PostGetInt('testPost_NUM'));
        $this->assertSame(true, Request::PostGetBool('testPost_TRUE'));
        $this->assertSame(false, Request::PostGetBool('testPost_FALSE'));
    }

    public function testGet()
    {
        $_GET['testGet_STR'] = 'test val';
        $_GET['testGet_NUM'] = 2.2;
        $_GET['testGet_TRUE'] = 1;
        $_GET['testGet_FALSE'] = 'false';
        $this->assertSame('test val', Request::GetStr('testGet_STR'));
        $this->assertSame(2, Request::GetInt('testGet_NUM'));
        $this->assertSame(2.2, Request::GetFloat('testGet_NUM'));
        $this->assertSame(true, Request::GetBool('testGet_TRUE'));
        $this->assertSame(false, Request::GetBool('testGet_FALSE'));

        $this->assertSame('test val', Request::PostGetStr('testGet_STR'));
        $this->assertSame(2, Request::PostGetInt('testGet_NUM'));
        $this->assertSame(true, Request::PostGetBool('testGet_TRUE'));
        $this->assertSame(false, Request::PostGetBool('testGet_FALSE'));

        $this->assertNull(Request::PostGetStr('testNull_STR'));
        $this->assertNull(Request::PostGetInt('testNull_NUM'));
        $this->assertSame(false, Request::PostGetBool('testNull_FALSE'));
    }

    public function testGetStringZero()
    {
        $_GET['testGet_STR1'] = '';
        $_GET['testGet_STR2'] = '0';
        $_GET['testGet_STR3'] = '   ';

        $this->assertSame('', Request::GetStr('testGet_STR1'));
        $this->assertSame('0', Request::GetStr('testGet_STR2'));
        $this->assertSame('', Request::GetStr('testGet_STR3'));

        // test one that doesn't exist
        $this->assertSame('', Request::GetStr('testGet_STR4'));
    }

    public function testPostPrefixStr()
    {
        $_POST['one'] = 1;
        $_POST['two'] = 2;
        $_POST['three'] = 3;
        $_POST['four'] = 4;
        $_POST['four_one'] = 41;
        $_POST['four_two'] = 42;
        $_POST['four_three'] = 43;
        $_POST['four_four'] = 44;

        $found = Request::PostPrefixStr('four_');

        $this->assertCount(4, $found);
        $this->assertArrayHasKey('four_four', $found);
        $this->assertEquals(44, $found['four_four']);
    }

    public function testUrlParts()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/dir1/dir2/dir3/page?arg=1';

        $parts = Request::UrlParts();

        $this->assertCount(4, $parts);
        $this->assertEquals('page', $parts[3]);
    }

    public function testPut()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->assertSame('1', Request::PutStr('a'));
        $this->assertSame(2, Request::PutInt('b'));
        $this->assertSame('', Request::PutStr('y'));
        $this->assertSame(0, Request::PutInt('z'));
    }

    public function testDelete()
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $this->assertSame('1', Request::DeleteStr('a'));
        $this->assertSame(2, Request::DeleteInt('b'));
        $this->assertSame('', Request::DeleteStr('y'));
        $this->assertSame(0, Request::DeleteInt('z'));
    }

    public function testUserIsBot()
    {
        $this->assertTrue(Request::UserIsBot());

        $_SERVER['HTTP_USER_AGENT'] = 'firefox ubuntu linux 18.04';
        $this->assertFalse(Request::UserIsBot());

        $_SERVER['HTTP_USER_AGENT'] = '';
        $this->assertTrue(Request::UserIsBot());

        $_SERVER['HTTP_USER_AGENT'] = 'firefox ubuntu bot linux 18.04';
        $this->assertTrue(Request::UserIsBot());

        $_SERVER['HTTP_USER_AGENT'] = 'firefox ubuntu linux 18.04 bing';
        $this->assertTrue(Request::UserIsBot());

        $_SERVER['HTTP_USER_AGENT'] = 'google firefox ubuntu linux 18.04';
        $this->assertTrue(Request::UserIsBot());
    }

    public function testIsHTTPS()
    {
        $_SERVER['HTTPS'] = 'off';
        $this->assertFalse(Request::IsHTTPS());

        $_SERVER['HTTPS'] = 'on';
        $this->assertTrue(Request::IsHTTPS());

        unset($_SERVER['HTTPS']);
        $this->assertFalse(Request::IsHTTPS());
    }

    public function testIsLocalhost()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $this->assertFalse(Request::IsLocalhost());

        $_SERVER['HTTP_HOST'] = 'rvw.localhost';
        $this->assertTrue(Request::IsLocalhost());

        $_SERVER['HTTP_HOST'] = 'localhost';
        $this->assertTrue(Request::IsLocalhost());

        unset($_SERVER['HTTP_HOST']);
        $this->assertFalse(Request::IsLocalhost());
    }

    public function testIsAjax()
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'bob';
        $this->assertFalse(Request::IsAjax());

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
        $this->assertTrue(Request::IsAjax());

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertTrue(Request::IsAjaxGet());
        $this->assertFalse(Request::IsAjaxPost());
        $this->assertFalse(Request::IsAjaxPut());
        $this->assertFalse(Request::IsAjaxDelete());

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertFalse(Request::IsAjaxGet());
        $this->assertTrue(Request::IsAjaxPost());
        $this->assertFalse(Request::IsAjaxPut());
        $this->assertFalse(Request::IsAjaxDelete());

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->assertFalse(Request::IsAjaxGet());
        $this->assertFalse(Request::IsAjaxPost());
        $this->assertTrue(Request::IsAjaxPut());
        $this->assertFalse(Request::IsAjaxDelete());

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $this->assertFalse(Request::IsAjaxGet());
        $this->assertFalse(Request::IsAjaxPost());
        $this->assertFalse(Request::IsAjaxPut());
        $this->assertTrue(Request::IsAjaxDelete());

        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
        $this->assertFalse(Request::IsAjax());
    }

    public function testIntDefault()
    {
        $_POST['testPost_NUM1'] = null;
        $_POST['testPost_NUM2'] = '';
        $_POST['testPost_NUM2a'] = '  ';
        $_POST['testPost_NUM3'] = 0;
        $_POST['testPost_NUM4'] = '0.00';

        $_GET['testPost_NUM1'] = null;
        $_GET['testPost_NUM2'] = '';
        $_GET['testPost_NUM2a'] = '  ';
        $_GET['testPost_NUM3'] = 0;
        $_GET['testPost_NUM4'] = '0.00';

        $this->assertSame(-1, Request::PostInt('testPost_NUM1', -1));
        $this->assertSame(-1, Request::PostInt('testPost_NUM2', -1));
        $this->assertSame(-1, Request::PostInt('testPost_NUM2a', -1));
        $this->assertSame(0, Request::PostInt('testPost_NUM3', -1));
        $this->assertSame(0, Request::PostInt('testPost_NUM4', -1));

        $this->assertSame(-1, Request::GetInt('testPost_NUM1', -1));
        $this->assertSame(-1, Request::GetInt('testPost_NUM2', -1));
        $this->assertSame(-1, Request::GetInt('testPost_NUM2a', -1));
        $this->assertSame(0, Request::GetInt('testPost_NUM3', -1));
        $this->assertSame(0, Request::GetInt('testPost_NUM4', -1));
    }
}
