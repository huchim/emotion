<?php namespace Emotion\Configuration\File;

use \Emotion\Contracts\Configuration\IConfigurationProvider;
use \Emotion\Contracts\Configuration\IFileProvider;

class FileConfigurationProvider implements IConfigurationProvider {
    private $path = "";
    private $content = "";
    private $loaded = false;

    /**
     * Undocumented variable
     *
     * @var FileConfigurationSource
     */
    private $source = null;

    /**
     * Undocumented function
     *
     * @param FileConfigurationSource $source
     */
    public function __construct(FileConfigurationSource $source) {
        $this->source = $source;
    }

    public function setContent($content) {
        $this->loaded = true;
        $this->content = $content;
    }

    public function getContent() {
        return $this->content;
    }

    public function load($reload = false) {
        if ($this->loaded && !$reload) {
            return;
        }

        if ($this->source === null) {
            throw new \Exception("No se ha definido la ruta al archivo.");
        }

        $file = $this->source->fileProvider;

        if ($file->exists()) {
            $content = $file->getContent();
            $this->setContent($content);
        }
    }

    public function set($key, $value) {
        throw new \Exception("No se ha implementado ninguna operación en este proveedor para recuperar los datos.");
    }

    public function getData() {
        throw new \Exception("No se ha implementado ninguna operación en este proveedor para recuperar los datos.");
    }
}