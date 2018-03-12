<?php namespace Emotion\Contracts;

interface ILogger {
    public function log($level, $eventId, $exception, $meta = [], $formatter = null);
    public function trace($eventId, $exception, $meta = []);
    public function debug($eventId, $exception, $meta = []);
    public function info($eventId, $exception, $meta = []);
    public function warn($eventId, $exception, $meta = []);
    public function error($eventId, $exception, $meta = []);
    public function fatal($eventId, $exception, $meta = []);
}