<?php namespace Emotion\Contracts\Configuration;

interface IFileProvider {
    public function setContent($content);
    public function getContent();
    public function getFileName();
    public function exists();
}
