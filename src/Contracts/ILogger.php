<?php namespace Emotion\Contracts;

interface ILogger {
    public function log($level, $eventId, $exception, $formatter = null);
    public function info($eventId, $exception);
    public function debug($eventId, $exception);
    public function warn($eventId, $exception);
    public function error($eventId, $exception);
    public function fatal($eventId, $exception);
}