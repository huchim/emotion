<?php namespace Emotion\Configuration\File;

use \Emotion\Contracts\Configuration\IFileProvider;

class MockFileProvider implements IFileProvider {
    private $content = null;

    public function __construct($content = null) {
        $this->setContent($content);
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function getContent() {
        return $this->content;
    }

    public function getFileName() {
        return "mockfile";
    }

    public function exists() {
        return true;
    }
}