<?php
include "CustomBootstrap.php";

use PHPUnit\Framework\TestCase;
use Emotion\Core;
use Emotion\JsonConfig;
use Emotion\Utils;
use Emotion\HttpContext;
use Emotion\Exceptions\ExceptionCodes;

class AppStaticFolderTest extends TestCase
{
    public function testExistsPublicFile() {
        // Debe retornar un error de encabezado si lo encontró
        $this->expectExceptionCode(ExceptionCodes::E_RESPONSE_HEADER_ERROR);
        HttpContext::server("REQUEST_URI", "/public/index.html");
        Core::run();
    }

    public function testNotExistsPublicFile() {
        // Debe retornar un error de encabezado si lo encontró
        $this->expectExceptionCode(ExceptionCodes::E_ROUTE_STATIC_FILE_NOTFOUND);
        HttpContext::server("REQUEST_URI", "/public/foo.html");
        Core::run();
    }

    public function setUp() {
        Core::clearRouter();
        Core::addStaticFolder("public");
    }

    public static function tearDownAfterClass() {
        Core::clearRouter();
        Core::setRouterBase("");
    }
}