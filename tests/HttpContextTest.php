<?php
use PHPUnit\Framework\TestCase;

class HttpContextTest extends TestCase
{
    public function setUp() {
        
    }

    public function test_valuesFromHttpContext()
    {
        $vars = ["server", "post", "get", "files", "request", "session", "env", "cookie"];
        $expected = "test1";

        foreach ($vars as $var) {
            \Emotion\HttpContext::$var("test", $expected);
            $actual = \Emotion\HttpContext::$var("test");
            $this->assertEquals($expected, $actual);
        }
    }

    public function test_serverNameIsemptyHttpContext()
    {
        $actual = \Emotion\HttpContext::server("SERVER_NAME");
        $this->assertNull($actual, "El servidor no debería estar configurado en esta prueba.");
    }
}