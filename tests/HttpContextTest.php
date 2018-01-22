<?php
use PHPUnit\Framework\TestCase;

use \Emotion\HttpContext;

class HttpContextTest extends TestCase
{
    public function setUp() {
        
    }

    public function test_valuesFromHttpContext()
    {
        $vars = ["server", "post", "get", "files", "request", "session", "env", "cookie"];
        $expected = "test1";

        foreach ($vars as $var) {
            HttpContext::$var("test", $expected);
            $actual = \Emotion\HttpContext::$var("test");
            $this->assertEquals($expected, $actual);
        }
    }
}