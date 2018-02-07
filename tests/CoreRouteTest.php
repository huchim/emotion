<?php
include "CustomBootstrap.php";

use PHPUnit\Framework\TestCase;
use Emotion\Core\Bootstrapper;
//use Emotion\JsonConfig;
use Emotion\Utils;
use Emotion\HttpContext;
use Emotion\Exceptions\ExceptionCodes;

define("APP_DEBUG", true);

class CoreRouteTest extends TestCase
{
    /**
     * instancia de la aplicación.
     *
     * @var Emotion\App2
     */
    private static $app = null;

    public static function setUpBeforeClass() {
        // Core::clearRouter();
        self::$app = new \Emotion\App();
        self::$app->setDirectoryBase("tests/");
    }

    /**
     * Ocurre un error al intentar recuperar una cadena de conexión que no existe.
     *
     * @return void
     */
    public function testGetNullConnection() {
        $this->expectExceptionCode(ExceptionCodes::E_CONNECTIONS_MISSING);
        $connection = self::$app->connectionStrings("test");
    }

    /**
     * La cadena de conexión debe tener al menos dos opciones.
     *
     * @return void
     */
    public function testGetConnection() {
        $actual = count(self::$app->connectionStrings("default"));
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
        $conn_keys = array_keys(self::$app->connectionStrings("default"));

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
        $this->assertInstanceOf(\Emotion\Security\CookieUnSecure::class, Bootstrapper::getCredentialRepository());
    }

    public function testRun() {
        $this->expectOutputString("Se ejecutó Index.");
        HttpContext::server("REQUEST_URI", "/Home");
        self::$app->run();
    }

    public function testNotFoundRoute() {
        $this->expectExceptionCode(ExceptionCodes::E_ROUTER_NOT_FOUND);
        HttpContext::server("REQUEST_URI", "/foo/bar/foo/bar/foo");
        self::$app->setRouterBase("");
        self::$app->run();
    }


    public function testNotFoundController() {
        $this->expectExceptionCode(ExceptionCodes::E_CONTROLLER_CLASS_NOT_FOUND);
        HttpContext::server("REQUEST_URI", "/Foo/Home/?foo=1");
        
        try {
            self::$app->run();
        } catch (\Exception $ex) {
            throw $ex->getPrevious();
        }
    }

    public function testRunWithBase() {
        $this->expectOutputString("Se ejecutó Index.");
        HttpContext::server("REQUEST_URI", "/base/Home/Index/?foo=bar");
        
        self::$app->clearRouter();
        self::$app->setRouterBase("/base/");

        self::$app->run();
    }
}