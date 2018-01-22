<?php namespace Emotion;

use Emotion\Exceptions\ExceptionCodes;

class JsonConfig {
    /**
     * Configuración
     *
     * @var \Emotion\JsonConfig
     */
    private static $instance = null;
    private $callbacks = [];

    private function __construct() {
        $this->callbacks["json"] = function($fileName) {

            if (!file_exists($fileName)) {
                throw new \Exception(ExceptionCodes::S_JSON_FILE_MISSING, ExceptionCodes::E_JSON_FILE_MISSING);
            }

            return json_decode(\file_get_contents($fileName), true);
        };
    }

    public function addCallback($name, $callback) {
        $this->callbacks["json"] = $callback;
    }

    public function readConfig($typeName, $fileName) {
        return $this->callbacks[$typeName]($fileName);
    }

    public static function addStreamReader($typeName, $callback) {
        $self = self::getInstance();
        $self->addCallback($typeName, $callback);
    }

    public static function getStreamReader($typeName, $fileName, $throwError = true) {
        $self = self::getInstance();

        if (!file_exists($fileName)) {
            if ($throwError) {
                throw new \Exception(ExceptionCodes::S_JSON_FILE_MISSING, ExceptionCodes::E_JSON_FILE_MISSING);
            }
        }

        try {
            return $self->readConfig($typeName, $fileName);
        } catch (\Exception $ex) {
            if ($throwError) {
                throw new \Exception(ExceptionCodes::S_JSON_READ_FAILED, ExceptionCodes::E_JSON_READ_FAILED, $ex);
            } else {
                return array();
            }
        }
    }

    public static function getJson($fileName) {
        return self::getStreamReader("json", $fileName);
    }

    /**
     * Undocumented function
     *
     * @param string $fileName Ruta de acceso al archivo.
     * @return array
     */
    public static function tryGetJson($fileName) {
        return self::getStreamReader("json", $fileName, false);
    }

    /**
     * 
     *
     * @return JsonConfig
     */
    public static function getInstance() {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }
}