<?php namespace Emotion\Contracts\Configuration;

interface IConfiguration {
    public function getValue($object);
    public function getConnectionString($string);
}

