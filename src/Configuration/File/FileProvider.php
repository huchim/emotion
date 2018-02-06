<?php namespace Emotion\Configuration\File;

use \Emotion\Contracts\Configuration\IFileProvider;

class FileProvider implements IFileProvider {
    private $filename = null;
    private $base = "";
    private $content = null;
    private $loaded = false;

    /**
     * Undocumented variable
     *
     * @var \Emotion\Contracts\ILogger
     */
    private $logger = null;

    public function __construct($filename = null, $base = "") {
        $this->filename = $filename;
        $this->base = $base;
        $this->logger = new \Emotion\Loggers\Logger(self::class);

        $this->logger->debug(0, "Cargando archivo {$this->base}{$this->filename}");
    }

    public function setContent($content) {
        $this->logger->debug(0, "Se ha establecido el contenido del archivo.");
        $this->loaded = true;
        $this->content = $content;
    }

    public function getContent() {
        $filename = $this->getFileName();
        $this->logger->debug(0, "Recuperando contenido del archivo: {$filename}");
        return file_get_contents($filename);
    }

    public function getFileName() {
        return $this->base . $this->filename;
    }

    public function exists() {
        return file_exists($this->getFileName());
    }
}