<?php namespace Emotion\Loggers;

use \Emotion\Contracts\ILogger;
use \Emotion\Core\EmotionClass;

class Logger implements ILogger {
    const trace = 0;
    const debug = 1;
    const information = 2;
    const warning = 3;
    const error = 4;
    const fatal = 5;
    private $instanceName = null;
    private $debug = false;

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

        if (defined("APP_DEBUG")) {
            $this->setDebugMode(APP_DEBUG);
        }
    }

    public function setDebugMode(bool $appDebug) {
        $this->debug = $appDebug;
    }

    public function log($level, $eventId, $exception, $formatter = null) {
        if (!$this->debug) {
            return;
        }

        if ($level > self::fatal || $level < self::trace) {
            throw new \Exception("Nivel de registro no válido.");
        }

        if ($formatter !== null) {
            $formatter($level, $eventId, $exception);
            return;
        }

        $tpl = "N: " . str_pad($this->logLevel[$level], 12) . "| EvId: {$eventId} | M: %s | C: {$this->instanceName}";
        $minLength = 20;
        if ($exception instanceof \Exception) {
            $log = sprintf($tpl, str_pad($exception->getMessage(), $minLength));
        } else {
            $log = sprintf($tpl, str_pad($exception, $minLength));
        }

        if ($level > self::information) {
            // throw new \Exception("No se permite debug en pruebas");
            file_put_contents('php://stderr', $log . "\n");
        } else {
            file_put_contents('php://stdout', $log . "\n");
        }
    }

    public function info($eventId, $exception) {
        $this->log(self::information, $eventId, $exception);
    }

    public function debug($eventId, $exception) {
        $this->log(self::debug, $eventId, $exception);
    }

    public function warn($eventId, $exception) {
        $this->log(self::warning, $eventId, $exception);
    }

    public function error($eventId, $exception) {
        $this->log(self::error, $eventId, $exception);
    }

    public function fatal($eventId, $exception) {
        $this->log(self::fatal, $eventId, $exception);
    }
}