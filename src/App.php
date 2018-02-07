<?php namespace Emotion;

use Emotion\Exceptions\ExceptionCodes;
use Emotion\Core\Bootstrapper;
use \Emotion\Contracts\IReadOnlyAppState;

class App extends Bootstrapper implements IReadOnlyAppState {
    /**
     * Undocumented variable
     *
     * @var \Emotion\Contracts\ILogger
     */
    private $logger = null;

    /**
     * Inicializa una nueva instancia de la clase App.
     * @param bool $loadDefaults Carga la configuración predeterminada del sitio.
     */
    public function __construct($loadDefaults = true) {
        parent::__construct();        
        $this->logger = new \Emotion\Loggers\Logger(self::class);
        $this->logger->trace(0, "Inicializando aplicación...");
        
        $currentScriptName = HttpContext::server('SCRIPT_FILENAME') ?? "";
        
        if ($currentScriptName != "") {
            $dirRoot = Utils::normalizePath(realpath(dirname($currentScriptName)));
            $this->logger->info(0, "Estableciendo directorio: {$dirRoot}");
            $this->setDirectoryBase($dirRoot);
        }
        
        if ($loadDefaults) {
            $this->loadDefaultConfiguration();
        }
    }

    public function run() {
        $this->logger->info(0, "Ejecutando aplicación");
        $router = $this->getRouter();

        if ($router === null) {
            throw new Exceptions\ErrorException(
                ExceptionCodes::S_ROUTER_INVALID, 
                ExceptionCodes::E_ROUTER_INVALID);
        }

        $this->logger->debug(0, "Recuperando lista de rutas definidas.");
        $routesList = $router->getRoutes();

        // Si no se ha configurado ninguna ruta, agrego las predeterminadas:
        if (count($routesList) === 0) {
            $this->logger->warn(0, "No existe ninguna ruta definida, se agregarán las predeterminadas.");
            $this->addStaticFiles();
            $this->addMvcApi();
            $this->addMvc();
        }

        $requestUri = HttpContext::server("REQUEST_URI");
        $requestMethod = HttpContext::server("REQUEST_METHOD");
        $urlBase = $this->getRouterBase();

        $this->logger->info(0, "Uri: {$requestUri}::{$requestMethod}");
        $match = $router->match($requestUri, $requestMethod);

        // Definer resultado activo del enrutador.
        $this->setRouterResults($match["params"]);

        // Ejecutar la ruta o devolver un error 404.
        if( $match && is_callable( $match['target'] ) ) {
            // Código usualmente en \Emotion\Routes\RouteExtra
            $this->logger->debug(0, "El resultado se puede ejecutar.");
            $c = call_user_func_array( $match['target'], $match['params'] ); 
        } else {
            $this->logger->warn(0, "No se pudo coincidir ninguna ruta.");
            try {
                header(HttpContext::server("SERVER_PROTOCOL") . ' 404 Not Found');
            } catch (\Exception $ex) {
                // Recuperar una lista de rutas y reglas para mostrar.
                foreach ($routesList as $routeName => $route) {
                    $ex = new \Exception("No se pudo coincidir \"{$requestUri}\" (base: {$urlBase}) con {$routeName} bajo la regla {$route[1]}", 0, $ex);
                }

                throw new \Emotion\Exceptions\RouteException(
                    ExceptionCodes::S_ROUTER_NOT_FOUND,
                    ExceptionCodes::E_ROUTER_NOT_FOUND,
                    $ex);
            }
            
            $this->logger->warn(0, "Error: 404. Rutas: " . count($routesList));

            foreach ($routesList as $routeName => $route) {
                $this->logger->debug(0, "\"{$requestUri}\" (base: {$urlBase}) con {$routeName} bajo la regla {$route[1]}");
            }
        }
    }
}