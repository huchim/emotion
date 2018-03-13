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
    private $context = "";

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

    public function context($context = "") {
        $this->context = $context;
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

        $tpl = "N: " . str_pad($this->logLevel[$level], 12) . "| EvId: {$eventId} | M: %s | D: %s | C: {$this->instanceName}%s";
        $minLength = 20;
        $meta1 = is_array($meta) ? json_encode($meta) : "";

        if ($exception instanceof \Exception) {
            $message = str_pad($exception->getMessage(), $minLength);
        } else {
            $message = str_pad($exception, $minLength);
        }

        $context = $this->context;

        $log = sprintf(
            $tpl,
            $message,
            $meta1,
            $context
        );

        if ($level > self::information) {
            // throw new \Exception("No se permite debug en pruebas");
            file_put_contents('php://stderr', $log . "\n");
        } else {
            file_put_contents('php://stdout', $log . "\n");
        }
    }
    
    private function getContext($trace) {
        if ($this->context === null) {
            return null;
        }

        if ($this->context !== "") {
            return $this->context;
        }

        // Get from trace...
        if ($trace === null) {
            return "";
        }

        $x = array_reverse($trace);

        if (count($x) <= 1) {
            return "invalid";
        }

        return isset($x[0]["function"]) ? $x[0]["function"] : "unknown";
    }

    private function backtraceEnabled() {
        return $this->context !== null && $this->context === "";
    }

    public function debug($eventId, $exception, $meta = []) {
        // Only configure backtrace if context is not disabled.
        $this->context = $this->getContext($this->backtraceEnabled() ? debug_backtrace() : null);
        $this->log(self::debug, $eventId, $exception, $meta = []);
    }
    
    public function trace($eventId, $exception, $meta = []) {
        // Only configure backtrace if context is not disabled.
        $this->context = $this->getContext($this->backtraceEnabled() ? debug_backtrace() : null);
        $this->log(self::trace, $eventId, $exception, $meta = []);
    }

    public function info($eventId, $exception, $meta = []) {
        // Only configure backtrace if context is not disabled.
        $this->context = $this->getContext($this->backtraceEnabled() ? debug_backtrace() : null);
        $this->log(self::information, $eventId, $exception, $meta = []);
    }

    public function warn($eventId, $exception, $meta = []) {
        // Only configure backtrace if context is not disabled.
        $this->context = $this->getContext($this->backtraceEnabled() ? debug_backtrace() : null);
        $this->log(self::warning, $eventId, $exception, $meta = []);
    }

    public function error($eventId, $exception, $meta = []) {
        // Only configure backtrace if context is not disabled.
        $this->context = $this->getContext($this->backtraceEnabled() ? debug_backtrace() : null);
        $this->log(self::error, $eventId, $exception, $meta = []);
    }

    public function fatal($eventId, $exception, $meta = []) {
        // Only configure backtrace if context is not disabled.
        $this->context = $this->getContext($this->backtraceEnabled() ? debug_backtrace() : null);
        $this->log(self::fatal, $eventId, $exception, $meta = []);
    }
}