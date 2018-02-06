<?php namespace Emotion\Core;

class RouteCore extends ConfigurationExtensions {
    /**
     * Undocumented variable
     *
     * @var \Emotion\Contracts\ILogger
     */
    private $logger = null;

    /**
     * Enrutador
     * 
     * @var \AltoRouter
     */
    public $router = null;

    private $routeResult = null;

    public function __construct() {
        parent::__construct();
        $this->logger = new \Emotion\Loggers\Logger(self::class);
    }

    public function setRouterResults($routeResult) {
        $this->routeResult = $routeResult;
        $this->configuration->updateValue("__map_results", $this->routeResult);
    }

    public function getRouterResults() {
        return $this->routeResult;
    }

    /**
     * Devuelve la instancia del enrutador.
     *
     * @return \AltoRouter
     */
    public function getRouter() {
        if ($this->router === null) {
            $this->logger->debug(0, "El enrutador no ha sido inicializado.");
            // En caso de que no haya sido inicializado anteriormente.
            $this->router = new \AltoRouter();
        } else {
            $this->logger->debug(0, "Recuperando enrutador inicializado.");
        }

        return $this->router;
    }

    public function clearRouter() {
        $this->logger->debug(0, "Reinicializando enrutador a un nueva instancia.");
        $this->router = new \AltoRouter();
    }    
}