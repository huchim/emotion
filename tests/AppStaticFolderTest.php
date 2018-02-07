<?php
include "CustomBootstrap.php";

use PHPUnit\Framework\TestCase;
use Emotion\HttpContext;
use Emotion\Exceptions\ExceptionCodes;

class AppStaticFolderTest extends TestCase
{
    private static $app = null;

    public function setUp() {
        if (!defined("APP_DEBUG")) define("APP_DEBUG", true);

        self::$app = new \Emotion\App();
        self::$app->setDirectoryBase("tests/");
        self::$app->loadDefaultConfiguration();        
        self::$app->addStaticFolder("public");
    }

    public function testExistsPublicFile() {
        // Debe retornar un error de encabezado si lo encontró
        $this->expectExceptionCode(ExceptionCodes::E_RESPONSE_HEADER_ERROR);
        HttpContext::server("REQUEST_URI", "/public/index.html");
        self::$app->run();
    }

    public function testNotExistsPublicFile() {
        // Debe retornar un error de encabezado si lo encontró
        $this->expectExceptionCode(ExceptionCodes::E_ROUTE_STATIC_FILE_NOTFOUND);
        HttpContext::server("REQUEST_URI", "/public/foo.html");
        self::$app->run();
    }
}