<?php namespace Emotion\Configuration;

class CoreConfiguration
{
    protected static $instance = null;
    private $_config = null;    
    private $_customConfigurationJson = Array();
    private $basePath = "";
    private $debugMode = false;

    private function __construct() {
        $this->_config = new \Emotion\Configuration\DirectoryConfiguration();
    }

    protected function __clone() {
    }

    public function loadConfig($fileName) {
        if (!file_exists($fileName)) {
            $this->initializeJsonFile($fileName);
        }

        $fileContent = json_decode(\file_get_contents($fileName), true);
        $this->_config->fromArray($fileContent["paths"]);

        if (isset($fileContent["debug"])) {
            $this->debugMode =  $fileContent["debug"];
        }

        if (isset($fileContent["basePath"])) {
            $this->basePath =  $fileContent["basePath"];
        }
    }

    private function initializeJsonFile($fileName) {
        $config = array(
            "paths" => $this->_config->toArray(),
            "debug" => $this->debugMode,
            "basePath" => $this->basePath,
        );

        file_put_contents($fileName, json_encode($config, JSON_PRETTY_PRINT));
    }

    /**
     * Devuelve la configuración de carpetas de la aplicación.
     *
     * @return \Emotion\Configuration\DirectoryConfiguration
     */
    public function getConfig() {
        return $this->_config;
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

    public function initialize($configDir) {
        // Raíz de la aplicación.
        $this->_config = $configDir;
    }

    public function getWebSiteIni() {
        return tempnam(\sys_get_temp_dir(), "siap");
        // TODO: Determinar un buen lugar para almacenar el website.ini y si es necesario.
        ////return $this->_config->root . "website.ini";
    }
}