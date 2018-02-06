<?php namespace Emotion;

use Emotion\Exceptions\ExceptionCodes;
use Emotion\Core\Bootstrapper;
use \Emotion\Contracts\Configuration\IConfigurationRoot;
use \Emotion\Contracts\IReadOnlyAppState;

class App2 extends Bootstrapper implements IReadOnlyAppState {
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

    public function run() {
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

        $match = $router->match($requestUri, $requestMethod);

        // Definer resultado activo del enrutador.
        $this->setRouterResults($match["params"]);

        // Ejecutar la ruta o devolver un error 404.
        if( $match && is_callable( $match['target'] ) ) {
            // Código usualmente en \Emotion\Routes\RouteExtra
            $c = call_user_func_array( $match['target'], $match['params'] ); 
        } else {
            // no route was matched
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
        }
    }
}