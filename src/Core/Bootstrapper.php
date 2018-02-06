<?php namespace Emotion\Core;

use \Emotion\Core\Routes\RouteExtra;

class Bootstrapper extends RouteExtra {
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

    public function loadDefaultConfiguration() {
        $this->logger->info(0, "Cargando configuración predeterminada.");

        // Carga la configuración inicial requerida.
        $this->loadConfigurationArray([
            "debug" => false,
            "src" => "",
            "mvc" => "app",
            "api" => "",
            "controllerName" => "Home",
            "controllerAction" => "Index",
        ]);

        $this->loadConfigurationJsonFiles(["package.json", "app.json"]);

        // Genera la confuración.
        $this->buildConfiguration();
    }

    /**
     * Devuelve el encargado de administrar la sesión del usuario.
     *
     * @return \Emotion\Security\ICredentialRepository
     */
    public static function getCredentialRepository() {
        return new \Emotion\Security\CookieUnSecure();
    }
}