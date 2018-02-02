<?php namespace Emotion\Contracts\Configuration;

interface IConfigurationProvider {
    public function load($reload = false);
    public function set($key, $value);
    public function getData();
}