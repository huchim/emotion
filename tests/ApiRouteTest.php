<?php
include "CustomBootstrap.php";

use PHPUnit\Framework\TestCase;
use Emotion\Core;
use Emotion\JsonConfig;
use Emotion\Utils;
use Emotion\HttpContext;
use Emotion\Exceptions\ExceptionCodes;

class ApiRouteTest extends TestCase
{
    public function testNotFoundRoute() {
        $this->expectExceptionCode(ExceptionCodes::E_ROUTER_NOT_FOUND);
        HttpContext::server("REQUEST_URI", "api/foo/bar/foo/bar/foo");
        Core::run();
    }

    public function testNotFoundController() {
        $this->expectExceptionCode(ExceptionCodes::E_CONTROLLER_CLASS_NOT_FOUND);
        HttpContext::server("REQUEST_URI", "api/siap/Home2/?foo=1");
        
        try {
            Core::run();
        } catch (\Exception $ex) {
            throw $ex->getPrevious();
        }
    }

    public function testRunWithBase() {
        $this->expectOutputString("JSON");
        HttpContext::server("REQUEST_URI", "/base/api/Home2/Index/?foo=1");
        Core::setRouterBase("/base/");
        Core::run();
    }

    public static function setUpBeforeClass() {
        Core::clearRouter();
        Core::addMvcApi();
    }

    public static function tearDownAfterClass() {
        Core::clearRouter();
        Core::setRouterBase("");
    }
}