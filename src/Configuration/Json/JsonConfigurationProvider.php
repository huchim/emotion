<?php namespace Emotion\Configuration\Json;

use \Emotion\Configuration\File\FileConfigurationProvider;

class JsonConfigurationProvider extends FileConfigurationProvider {
    private $data = [];

    /**
     * Undocumented function
     *
     * @param JsonConfigurationSource $source
     */
    public function __construct(JsonConfigurationSource $source) {
        parent::__construct($source);
    }

    public function load($reload = false) {
        parent::load($reload);

        // Analizar datos.
        $raw = $this->getContent();

        if ($raw === "") {
            $raw = [];
        } else {
            $this->data = json_decode($raw, true);
        }
    }

    public function getData() 
    {
        return $this->data;
    }

    public function set($key, $value) {
        $this->data[$key] = $value;
    }
}