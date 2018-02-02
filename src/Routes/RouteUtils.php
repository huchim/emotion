<?php namespace Emotion\Routes;

use \Emotion\Configuration\ConfigurationCore;
use \Emotion\Exceptions\ExceptionCodes;

class RouteUtils extends RouteCore {
    private static $routerBaseUrl = "";

    /**
	 * Map a route to a target
	 *
	 * @param string $method One of 5 HTTP Methods, or a pipe-separated list of multiple HTTP Methods (GET|POST|PATCH|PUT|DELETE)
	 * @param string $route The route regex, custom regex must start with an @. You can use multiple pre-set regex filters, like [i:id]
	 * @param mixed $target The target where this route should point to. Can be anything.
	 * @param string $name Optional name of this route. Supply if you want to reverse route this url in your application.
	 * @throws Exception
	 */
	public static function map($method, $route, $target, $name = null) {
		self::getRouter()->map($method, $route, $target, $name);
    }

    public static function setRouterBase($routerBaseUrl) {
        self::$routerBaseUrl = $routerBaseUrl;
        self::getRouter()->setBasePath(self::$routerBaseUrl);
    }

    public static function getRouterBase() {
        return self::$routerBaseUrl;
    }

    public static function formatRouteRule($routeRule) {
        // Hay que determinar si existe una base a la URL para
        // poder agregar el prefijo correctamente.
        $routerBase = self::$routerBaseUrl;
        $startWithSlash = substr($routeRule, 0, 1) === "/";
        
        if (!$startWithSlash && $routerBase === "") {
            // Si no existe una URL base, se debe comenzar con una barra diagonal.
            $routeRule = "/{$routeRule}";
        }

        if ($startWithSlash && $routerBase !== "") {
            // Si existe una URL base, no se debe comenzar con una barra diagonal.
            $routeRule = substr($routeRule, 1);
        }

        return $routeRule;
    }

    public static function serve($file, $baseDir = "public") {
        $filePath = "{$baseDir}/{$file}";

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