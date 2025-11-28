<?php
/** @noinspection PhpArrayWriteIsNotUsedInspection */

namespace SIL\Tests\Webonary;

use PHPUnit\Framework\TestCase;
use SIL\Tests\Mocks\MockFilterInput;

class MockFilterInputTest extends TestCase
{
    public function testFilterInput()
    {
        $_POST = ['post_val' => 'one'];
        $_GET = ['get_val' => 'two'];
        $_SERVER = ['server_val' => 'three'];

        $this->assertEquals('one', MockFilterInput::FilterInput(INPUT_POST, 'post_val'));
        $this->assertEquals('two', MockFilterInput::FilterInput(INPUT_GET, 'get_val'));
        $this->assertEquals('three', MockFilterInput::FilterInput(INPUT_SERVER, 'server_val'));
        $this->assertEquals('', MockFilterInput::FilterInput(INPUT_POST, 'post_val_1'));
    }
}
