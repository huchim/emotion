<?php namespace Emotion\Contracts\Configuration;

interface IFileConfigurationSource {
    public function setPath($path);
    public function getPath();
    public function setContent($content);
    public function getContent();
    public function load();
}