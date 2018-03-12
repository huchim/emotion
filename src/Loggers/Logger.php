<?php namespace Emotion\Loggers;

use \Emotion\Contracts\ILogger;
use \Emotion\Utils;

class Logger implements ILogger {
    const trace = 0;
    const debug = 1;
    const information = 2;
    const warning = 3;
    const error = 4;
    const fatal = 5;
    private $instanceName = null;
    private $debug = false;
    private $minimumLevel = self::error;

    private $logLevel = [
        "trace",
        "debug",
        "information",
        "warning",
        "error",
        "fatal"
    ];

    public function __construct($instanceName) {
        $this->instanceName = $instanceName;

        // Definir si se generará un registro de mensaje.
        $this->debug = Utils::isDebug();

        if (defined("APP_DEBUG_LEVEL")) {
            $this->minimumLevel = APP_DEBUG_LEVEL;
        }

        if ($this->minimumLevel < self::trace && $this->minimumLevel > self::fatal) {
            throw new \Exception("El nivel definido de error no está permitido.");
        }
    }

    public function setDebugMode(bool $appDebug) {
        $this->debug = $appDebug;
    }

    public function log($level, $eventId, $exception, $meta = [], $formatter = null) {
        if (!$this->debug || $level < $this->minimumLevel) {
            return;
        }

        if ($level > self::fatal || $level < self::trace) {
            throw new \Exception("Nivel de registro no válido.");
        }

        if ($formatter !== null) {
            $formatter($level, $eventId, $exception);
            return;
        }

        $tpl = "N: " . str_pad($this->logLevel[$level], 12) . "| EvId: {$eventId} | M: %s | D: %s | C: {$this->instanceName}";
        $minLength = 20;
        $meta1 = is_array($meta) ? json_encode($meta) : "";

        if ($exception instanceof \Exception) {
            $log = sprintf($tpl, str_pad($exception->getMessage(), $minLength));
        } else {
            $log = sprintf($tpl, str_pad($exception, $minLength), $meta1);
        }

        if ($level > self::information) {
            // throw new \Exception("No se permite debug en pruebas");
            file_put_contents('php://stderr', $log . "\n");
        } else {
            file_put_contents('php://stdout', $log . "\n");
        }
    }
    
    public function debug($eventId, $exception, $meta = []) {
        $this->log(self::debug, $eventId, $exception, $meta = []);
    }
    
    public function trace($eventId, $exception, $meta = []) {
        $this->log(self::trace, $eventId, $exception, $meta = []);
    }

    public function info($eventId, $exception, $meta = []) {
        $this->log(self::information, $eventId, $exception, $meta = []);
    }

    public function warn($eventId, $exception, $meta = []) {
        $this->log(self::warning, $eventId, $exception, $meta = []);
    }

    public function error($eventId, $exception, $meta = []) {
        $this->log(self::error, $eventId, $exception, $meta = []);
    }

    public function fatal($eventId, $exception, $meta = []) {
        $this->log(self::fatal, $eventId, $exception, $meta = []);
    }
}