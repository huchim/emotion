<?php namespace Emotion\Core\Routes;

use \Emotion\Core\RouteCore;
use \Emotion\Exceptions\ExceptionCodes;

class RouteUtils extends RouteCore {
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

    /**
	 * Map a route to a target
	 *
	 * @param string $method One of 5 HTTP Methods, or a pipe-separated list of multiple HTTP Methods (GET|POST|PATCH|PUT|DELETE)
	 * @param string $route The route regex, custom regex must start with an @. You can use multiple pre-set regex filters, like [i:id]
	 * @param mixed $target The target where this route should point to. Can be anything.
	 * @param string $name Optional name of this route. Supply if you want to reverse route this url in your application.
	 * @throws Exception
	 */
	public function map($method, $route, $target, $name = null) {
		$this->getRouter()->map($method, $route, $target, $name);
    }

    public function setRouterBase($routerBaseUrl) {
        parent::setRouterBase($routerBaseUrl);
        $this->getRouter()->setBasePath($this->RouteUrlBase);
    }

    public function formatRouteRule($routeRule) {
        // Hay que determinar si existe una base a la URL para
        // poder agregar el prefijo correctamente.
        $routerBase = $this->getRouterBase() ?? "";
        $startWithSlash = substr($routeRule, 0, 1) === "/";

        $this->logger->debug(0, "URL Base: \"{$routerBase}\". " . ($startWithSlash ? "Inicia con diagonal" : "No inicia con diagonal."));
        
        if (!$startWithSlash && $routerBase === "") {
            // Si no existe una URL base, se debe comenzar con una barra diagonal.
            $this->logger->debug(0, "La regla requiere de una diagonal al inicio.");
            $routeRule = "/{$routeRule}";
        }

        if ($startWithSlash && $routerBase !== "") {
            // Si existe una URL base, no se debe comenzar con una barra diagonal.
            $this->logger->debug(0, "La regla no requiere una diagonal al inicio, se quitará.");
            $routeRule = substr($routeRule, 1);
        }

        return $routeRule;
    }

    public static function serve($file, $baseDir = "public") {
        $filePath = "{$baseDir}/{$file}";

        if ($file === "") {
            throw new \Emotion\Exceptions\InternalException(
                sprintf(ExceptionCodes::S_ROUTE_STATIC_FILE_EMPTY, $baseDir),
                ExceptionCodes::E_ROUTE_STATIC_FILE_EMPTY
            );
        }

        if (!file_exists($filePath)) {
            throw new \Emotion\Exceptions\InternalException(
                sprintf(ExceptionCodes::S_ROUTE_STATIC_FILE_NOTFOUND, $file, $baseDir),
                ExceptionCodes::E_ROUTE_STATIC_FILE_NOTFOUND
            );
        }

        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimesSupported = array(
            "css" => " 	text/css",
            "png" => "image/png",
            "gif" => "image/gif",
            "jpg" => "image/jpeg",
            "js" => "application/x-javascript",
            "txt" => "text/plain",
            "html" => "text/html",
        );

        $selectedMime = "text/plain";

        if (isset($mimesSupported[$fileExtension])) {
            $selectedMime = $mimesSupported[$fileExtension];
        }

        $contentTypeHeader = "Content-Type: {$selectedMime}";

        try
        {
            // Enviar el encabezado correcto.
            header($contentTypeHeader);
        } catch (\Exception $ex)
        {
            throw new \Emotion\Exceptions\InternalException(
                sprintf(ExceptionCodes::S_RESPONSE_HEADER_ERROR, $contentTypeHeader),
                ExceptionCodes::E_RESPONSE_HEADER_ERROR,
                $ex
            );
        }
        
        // Enviar el contenido al navegador.
        echo file_get_contents($filePath);
    }
}