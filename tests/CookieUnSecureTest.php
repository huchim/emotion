<?php
use PHPUnit\Framework\TestCase;

class CookieUnSecureTest extends TestCase
{
    private $instance = null;

    public function setUp() {
        $this->instance = new \Emotion\Security\CookieUnSecure();
    }

    public function test_getToken()
    {
        $this->assertEquals(
            $this->instance->getToken(), "]Mh+CuyQ.zcMcXU-;EsU_c#=`L_L9QnP"
        );
    }
}