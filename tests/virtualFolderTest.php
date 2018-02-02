<?php
include "CustomBootstrap.php";

use PHPUnit\Framework\TestCase;
use Emotion\Core;
use Emotion\JsonConfig;
use Emotion\Utils;
use Emotion\HttpContext;
use Emotion\Exceptions\ExceptionCodes;

class virtualFolderTest extends TestCase
{
    public function testExistsDefaultPublicFile() {
        // Debe retornar un error de encabezado si lo encontró
        // $this->expectExceptionCode(ExceptionCodes::E_RESPONSE_HEADER_ERROR);
        HttpContext::server("REQUEST_URI", "/");

        Core::run();
    }

    public function testExistsPublicFile() {
        // Debe retornar un error de encabezado si lo encontró
        $this->expectExceptionCode(ExceptionCodes::E_RESPONSE_HEADER_ERROR);
        HttpContext::server("REQUEST_URI", "/index.html");
        Core::run();
    }

    public function testNotExistsPublicFile() {
        // Debe retornar un error de encabezado si lo encontró
        $this->expectExceptionCode(ExceptionCodes::E_ROUTE_STATIC_FILE_NOTFOUND);
        HttpContext::server("REQUEST_URI", "/foo.html");
        Core::run();
    }

    public function setUp() {
        Core::log("Eliminando rutas presentes en el ruteador.");
        Core::clearRouter();

        // Que todo lo que sea "raiz" se busque en "html".
        Core::log("Agregando ruta.");
        Core::addStaticFolder("public", "index.html", "");
    }

    public static function tearDownAfterClass() {
        Core::log("Terminando pruebas");
        Core::clearRouter();
        Core::setRouterBase("");
    }
}