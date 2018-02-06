<?php namespace Emotion\Core;

class ConfigurationExtensions extends Configuration {
    /**
     * Undocumented variable
     *
     * @var \Emotion\Contracts\ILogger
     */
    private $logger = null;

    public function __construct() {
        parent::__construct();
        $this->logger = new \Emotion\Loggers\Logger(self::class);
    }


    public function loadConfigurationArray($configArray) {
        $this->logger->debug(0, "Cargando configuración desde un arreglo.");
        $source = new \Emotion\Configuration\Memory\MemoryConfigurationSource();
        $source->config = $configArray;

        // Agregar al constructor.
        $this->addConfigurationSource($source);
    }

    public function loadConfigurationJsonFiles($files = array()) {
        $directoryBase = $this->DirectoryBase;
        $relative = strpos($directoryBase, "./") !== false;

        $this->logger->debug(0, "Cargando configuración desde JSON. Base: {$directoryBase}, relativo a " . ($relative ? "index.php" : "{$directoryBase}"));

        // Convertir a arreglo si es necesario.
        if (!is_array($files)) {
            $files = [$files];
        }

        // Crear los recursos de tipo JSON.
        foreach ($files as $file) {
            // Acceder al archivo.
            $this->logger->debug(0, "Inicializando archivo: {$file}");
            $fi = new \Emotion\Configuration\File\FileProvider($file, $relative ? null : $directoryBase);

            // Crear el recurso.
            $source = new \Emotion\Configuration\Json\JsonConfigurationSource();
            $source->fileProvider = $fi;

            // Agregar al constructor.
            $this->addConfigurationSource($source);
        }
    }
}