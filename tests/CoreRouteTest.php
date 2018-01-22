<?php
include "CustomBootstrap.php";

use PHPUnit\Framework\TestCase;
use Emotion\Core;
use Emotion\JsonConfig;
use Emotion\Utils;
use Emotion\HttpContext;
use Emotion\Exceptions\ExceptionCodes;

class CoreRouteTest extends TestCase
{
    /**
     * Ocurre un error al intentar recuperar una cadena de conexión que no existe.
     *
     * @return void
     */
    public function testGetNullConnection() {
        $this->expectExceptionCode(ExceptionCodes::E_CONNECTIONS_MISSING);
        $connection = Core::connectionStrings("test");
    }

    /**
     * La cadena de conexión debe tener al menos dos opciones.
     *
     * @return void
     */
    public function testGetConnection() {
        //$this->expectExceptionCode(ExceptionCodes::E_CONNECTIONS_MISSING);
        $actual = count(Core::connectionStrings("default"));
        $expected = 1;
        $this->assertGreaterThanOrEqual($expected, $actual);
    }

    /**
     * Verifica que la cadena de conexión contenga una clave válida de base de datos.\DeepCopy\f001\A
     *
     * @return void
     */
    public function testDatabaseInConnection() {
        $actual = false;
        $keys = ["database", "dbname", "db", "catalog", "initial catalog"];
        $conn_keys = array_keys(Core::connectionStrings("default"));

        foreach ($keys as $key) {
            if (in_array($key, $conn_keys)) {
                $actual = true;
                break;
            }
        }

        $this->assertTrue($actual);
    }

    /**
     * Confirma que el tipo predeterminado de segurida se mantenga.\DeepCopy\f001\A
     *
     * @return void
     */
    public function testGetDefaultSecurityHandler() {
        $this->assertInstanceOf(\Emotion\Security\CookieUnSecure::class, Core::getCredentialRepository());
    }

    public function testRun() {
        $this->expectOutputString("Se ejecutó Index.");
        HttpContext::server("REQUEST_URI", "Home");
        Core::run();
    }

    public function testNotFoundRoute() {
        $this->expectExceptionCode(ExceptionCodes::E_ROUTER_NOT_FOUND);
        HttpContext::server("REQUEST_URI", "/foo/bar/foo/bar/foo");
        Core::setRouterBase("");
        Core::run();
    }


    public function testNotFoundController() {
        $this->expectExceptionCode(ExceptionCodes::E_CONTROLLER_CLASS_NOT_FOUND);
        HttpContext::server("REQUEST_URI", "siap/Home/?foo=1");
        
        try {
            Core::run();
        } catch (\Exception $ex) {
            throw $ex->getPrevious();
        }
    }

    public function testRunWithBase() {
        $this->expectOutputString("Se ejecutó Index.");
        HttpContext::server("REQUEST_URI", "/base/Home/Index/?foo=1");
        Core::setRouterBase("/base/");
        Core::run();
    }
}