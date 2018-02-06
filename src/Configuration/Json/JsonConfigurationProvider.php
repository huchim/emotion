<?php namespace Emotion\Configuration\Json;

use \Emotion\Configuration\File\FileConfigurationProvider;

class JsonConfigurationProvider extends FileConfigurationProvider {
    private $data = [];
    /**
     * Undocumented variable
     *
     * @var \Emotion\Contracts\ILogger
     */
    private $logger = null;

    /**
     * Undocumented function
     *
     * @param JsonConfigurationSource $source
     */
    public function __construct(JsonConfigurationSource $source) {
        parent::__construct($source);
        $this->logger = new \Emotion\Loggers\Logger(self::class);
    }

    public function load($reload = false) {
        parent::load($reload);

        $this->logger->debug(0, "Recuperando contenido del archivo.");
        $raw = $this->getContent();

        if ($raw === "") {
            $this->logger->warn(0, "El archivo esta vacío.");
            $raw = [];
        } else {
            $this->logger->debug(0, "Convirtiendo a arreglo.");
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