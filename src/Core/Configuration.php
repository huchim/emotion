<?php namespace Emotion\Core;

use \Emotion\Contracts\Configuration\IConfigurationRoot;
use \Emotion\Contracts\Configuration\IConfigurationSource;
use \Emotion\Contracts\Configuration\IConfigurationBuilder;
use \Emotion\Exceptions\ExceptionCodes;

class Configuration {
    /**
     * Undocumented variable
     *
     * @var \Emotion\Contracts\ILogger
     */
    private $logger = null;

    /**
     * Configuración de la aplicación.
     *
     * @var \Emotion\Contracts\Configuration\IConfigurationRoot
     */
    public $configuration = null;

    /**
     * Obtiene o establece la carpeta base de la aplicación.
     *
     * @var string
     */
    public $DirectoryBase = "";

    /**
     * Obtiene o establece la carpeta base de la URL.
     *
     * @var string
     */
    public $RouteUrlBase = "";

    /**
     * Undocumented variable
     *
     * @var \Emotion\Contracts\Configuration\IConfigurationBuilder
     */
    private $configurationBuilder = null;

    public function __construct() {
        $this->logger = new \Emotion\Loggers\Logger(self::class);

        // Inicializar configuración.
        $this->configurationBuilder = new \Emotion\Configuration\ConfigurationBuilder();
        $this->buildConfiguration();
    }

    /**
     * Configura el directorio raiz de la aplicación.
     *
     * @param string $directoryBase Directorio raiz de la aplicación.
     * @return void
     */
    public function setDirectoryBase($directoryBase) {
        $this->DirectoryBase = $directoryBase;
    }

    public function setRouterBase($routerBaseUrl) {
        $this->RouteUrlBase = $routerBaseUrl;

        // Esto ayuda a que se sepa de manera general
        $this->configuration->updateValue("RouteUrlBase", $this->RouteUrlBase);
    }

    public function getRouterBase() {
        return $this->configuration->getValue("RouteUrlBase");
    }

    public function buildConfiguration() {
        $this->configuration = $this->configurationBuilder->build();
    }

    public function addConfigurationSource(IConfigurationSource $source) {
        $this->configurationBuilder->add($source);
    }

    /**
     * Obtiene la configuración de la aplicación.
     *
     * @return \Emotion\Contracts\Configuration\IConfigurationRoot
     */
    public function getConfiguration() {
        return $this->configuration;
    }

    /**
     * Undocumented function
     *
     * @param \Emotion\Contracts\Configuration\IConfigurationSource[] $sources
     * @return void
     */
    public function loadConfigurationSources($sources = array()) {
        if ($sources == null) {
            throw new \Exception("La lista de origenes de configuración es nula.");
        }

        foreach ($sources as $source) {
            $this->addConfigurationSource($source);
        }

        $this->buildConfiguration();
    }

    /**
     * Devuelve una lista de propiedades de la conexión.
     *
     * @param string $connectionName Nombre de la conexión.
     * @return array
     */
    public function connectionStrings($connectionName) {
        $this->logger->debug(0, "Recuperando información de la cadena de conexión {$connectionName}");
        return $this->configuration->getConnectionString($connectionName);
    }
}
