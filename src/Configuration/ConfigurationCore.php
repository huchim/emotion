<?php namespace Emotion\Configuration;

use \Emotion\JsonConfig;

class ConfigurationCore
{
    protected static $instance = null;
    private $appStructureDirectory = null;    
    private $_customConfigurationJson = Array();
    private $basePath = "";
    private $debugMode = false;

    private function __construct() {
        $this->appStructureDirectory = new DirectoryConfiguration();
    }

    protected function __clone() {
    }

    public function loadConfigFromArray($options) {
        // Cargar la configuración de los directorios.
        if (isset($options["includes"])) {
            $this->appStructureDirectory->fromArray($options["includes"]);
        }

        if (isset($options["debug"])) {
            $this->debugMode =  $options["debug"];
        }

        if (isset($options["src"])) {
            $this->basePath =  $options["src"];
        }
    }

    public function loadConfig($fileName) {
        // Cargar el contenido del archivo a un arreglo.
        $options = JsonConfig::tryGetJson($fileName);

        $this->loadConfigFromArray($options);
    }

    /**
     * Devuelve la configuración de carpetas de la aplicación.
     *
     * @return \Emotion\Configuration\DirectoryConfiguration
     */
    public function getConfig() {
        return $this->appStructureDirectory;
    }

    public function setBasePath($basePath) {

        $this->basePath = $basePath;
    }

    public function getBasePath() {
        return $this->basePath;
    }

    public function enableDebug() {
        $this->debugMode = true;
    }

    public function isDebug() {
        return $this->debugMode;
    }

    /**
     * Devuelve una instancia única de la configuración.
     *
     * @return CoreConfiguration
     */
    public static function getInstance() {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    public static function getSourceDirectory($subFolder = "") {
        $self = self::getInstance();
        $path = $self->getBasePath();

        // Eliminar diagonoal final.
        if(substr($path, -1) === '/') {
            $path = substr($path, 0, -1);
        }

        if(substr($subFolder, 0, 1) === '/') {
            $subFolder = substr($subFolder, 1);
        }

        if ($subFolder !== "") {
            $subFolder = "/{$subFolder}";
        }

        return $path . $subFolder;
    }
}