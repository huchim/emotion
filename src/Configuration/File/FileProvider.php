<?php namespace Emotion\Configuration\File;

use \Emotion\Contracts\Configuration\IFileProvider;

class FileProvider implements IFileProvider {
    private $filename = null;
    private $base = "";
    private $content = null;
    private $loaded = false;

    public function __construct($filename = null, $base = "") {
        $this->filename = $filename;
        $this->base = $base;
    }

    public function setContent($content) {
        $this->loaded = true;
        $this->content = $content;
    }

    public function getContent() {
        return file_get_contents($this->getFileName());
    }

    public function getFileName() {
        return $this->base . $this->filename;
    }

    public function exists() {
        return file_exists($this->getFileName());
    }
}