<?php namespace Emotion\Contracts\Configuration;

interface IConfigurationRoot {
    public function getValue($key);
    public function getConnectionString($connectionName);
    public function asArray();
}