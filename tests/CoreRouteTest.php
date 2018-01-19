<?php
use PHPUnit\Framework\TestCase;
use Emotion\Core;
use Emotion\JsonConfig;
use Emotion\Utils;
use Emotion\Exceptions\ExceptionCodes;

class CoreRouteTest extends TestCase
{
    public function setUp() {
        // Interceptar los errores de la aplicación.
        Utils::registerErrorHandler();

        JsonConfig::addStreamReader("json", function($fileName) {
            $currentDir = dirname(__FILE__);
            $fileName = $currentDir."/".$fileName;            
            return json_decode(\file_get_contents($fileName), true);
        });
    }

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

    public function testDatabaseInConnection() {
        $expected = ["database", "dbname", "db", "catalog", "initial catalog"];
        $conn = Core::connectionStrings("default");

        
    }
}