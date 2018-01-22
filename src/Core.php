<?php namespace Emotion;

use Emotion\Routes\RouteController;
use Emotion\Exceptions\ExceptionCodes;
use Emotion\Configuration\ConfigurationCore;

class Core extends RouteController {
    /**
     * Configuración
     *
     * @var \Emotion\Configuration\ConfigurationCore
     */
    private $configuration = null;
    protected static $instance = null;
    private $routeResult = null;

    public $info = array();

    protected function __construct() {
        parent::__construct();
        $this->configuration = ConfigurationCore::getInstance();
        $this->init();
    }

    public function init() {
        // Unir la configuración de la aplicación.
        $this->info = array_merge(
            JsonConfig::tryGetJson("package.json"),
            JsonConfig::tryGetJson("app.json"));
            
        // Actualizar la configuración desde el arreglo.
        $this->configuration->loadConfigFromArray($this->info);

        if (isset($this->info["basePath"])) {
            self::setRouterBase($this->info["basePath"]);
        }
    }

    public static function loadConfig($fileName) {
        // Actualiza la configuración.
        $self = Core::getInstance();
        $self->configuration->loadConfig($fileName);
        $self->init();
    }

    public static function info() {
        return Core::getInstance()->info;
    }

    public static function get($option) {
        $self = Core::getInstance();

        if (isset($self->info[$option])) {
            return $self->info[$option];
        }

        return "";
    }

    /**
     * Devuelve una lista de propiedades de la conexión.
     *
     * @param string $connectionName Nombre de la conexión.
     * @return array
     */
    public static function connectionStrings($connectionName) {
        $connections = Core::get("connectionStrings");

        if (!is_array($connections)) {
            throw new \Exception(ExceptionCodes::S_CONNECTIONS_EMPTY, ExceptionCodes::E_CONNECTIONS_EMPTY);
        }

        if (!isset($connections[$connectionName])) {
            throw new \Exception(ExceptionCodes::S_CONNECTIONS_MISSING, ExceptionCodes::E_CONNECTIONS_MISSING);
        }

        $connectionParts = explode(";", $connections[$connectionName]);
        $connectionOptions = [];

        foreach ($connectionParts as $connectionOption) {
            $options = explode("=", $connectionOption);

            if (count($options) !== 2) {
                // Esta sección en la cadena no representa un patrón clave-valor.
                continue;
            }

            $optionName = strtolower($options[0]);
            $optionValue = $options[1];
            $connectionOptions[$optionName] = $optionValue;
        }
        
        return $connectionOptions;
    }

    public static function log($message) {
        // TODO: Deshabilitar cuando no sea necesario.
        $config = \Emotion\Configuration\ConfigurationCore::getInstance();

        if ($config->isDebug()) {
            file_put_contents('php://stderr', $message . "\n");
        }
    }

    /**
     * Devuelve el encargado de administrar la sesión del usuario.
     *
     * @return \Emotion\Security\ICredentialRepository
     */
    public static function getCredentialRepository() {
        return new \Emotion\Security\CookieUnSecure();
    }

    /**
     * Devuelve una instancia única de la configuración.
     *
     * @return Core
     */
    public static function getInstance() {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Obtener la configuración del sitio.
     *
     * @return \Emotion\Configuration\DirectoryConfiguration
     */
    public function getConfig() {
        return $this->configuration->getConfig();
    }

    public function setRouterResults($routeResult) {
        $this->routeResult = $routeResult;
    }

    public function getRouterResults() {
        return $this->routeResult;
    }

    public static function run() {
        $self = self::getInstance();

        // Analizar posibles resultados.
        $router = self::getRouter();

        if ($router === null) {
            throw new Exceptions\ErrorException(
                ExceptionCodes::S_ROUTER_INVALID, 
                ExceptionCodes::E_ROUTER_INVALID);
        }

        $routesList = $router->getRoutes();

        // Si no se ha configurado ninguna ruta, agrego las predeterminadas:
        if (count($routesList) === 0) {
            Core::addStaticFiles();
            Core::addMvcApi();
            Core::addMvc();
        }

        $requestUri = HttpContext::server("REQUEST_URI");
        $requestMethod = HttpContext::server("REQUEST_METHOD");

        $match = $router->match($requestUri, $requestMethod);

        // Definer resultado activo del enrutador.
        $self->setRouterResults($match["params"]);

        // Ejecutar la ruta o devolver un error 404.
        if( $match && is_callable( $match['target'] ) ) {
            // Código usualmente en \Emotion\Routes\RouteExtra
            $c = call_user_func_array( $match['target'], $match['params'] ); 
        } else {
            // no route was matched
            try {
                header(HttpContext::server("SERVER_PROTOCOL") . ' 404 Not Found');
            } catch (\Exception $ex) {
                throw new \Emotion\Exceptions\RouteException(
                    ExceptionCodes::S_ROUTER_NOT_FOUND,
                    ExceptionCodes::E_ROUTER_NOT_FOUND,
                    $ex);
            }
        }
    }
}