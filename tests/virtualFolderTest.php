<?php
include "CustomBootstrap.php";

use PHPUnit\Framework\TestCase;
use Emotion\Utils;
use Emotion\HttpContext;
use Emotion\Exceptions\ExceptionCodes;

class virtualFolderTest extends TestCase
{
    private static $app = null;

    public function setUp() {
        if (!defined("APP_DEBUG")) define("APP_DEBUG", true);

        self::$app = new \Emotion\App2();
        self::$app->setDirectoryBase("tests/");
        self::$app->loadDefaultConfiguration();     
        self::$app->addStaticFolder("public", "index.html", "");
    }

    public function testExistsDefaultPublicFile() {
        // Debe retornar un error de encabezado si lo encontró
        $this->expectExceptionCode(ExceptionCodes::E_RESPONSE_HEADER_ERROR);
        HttpContext::server("REQUEST_URI", "/");

        self::$app->run();
    }

    public function testExistsPublicFile() {
        // Debe retornar un error de encabezado si lo encontró
        $this->expectExceptionCode(ExceptionCodes::E_RESPONSE_HEADER_ERROR);
        HttpContext::server("REQUEST_URI", "/index.html");
        self::$app->run();
    }

    public function testNotExistsPublicFile() {
        // Debe retornar un error de encabezado si lo encontró
        $this->expectExceptionCode(ExceptionCodes::E_ROUTE_STATIC_FILE_NOTFOUND);
        HttpContext::server("REQUEST_URI", "/foo.html");
        self::$app->run();
    }
}