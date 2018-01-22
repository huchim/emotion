<?php namespace Emotion\Routes;

class RouteUtils extends RouteCore {

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

    public static function setRouterBase($basePath) {
        self::getRouter()->setBasePath($basePath);
    }

    public static function getRouterBase() {
        self::getRouter()->getBasePath();
    }

    public static function serve($file, $baseDir = "public") {
        $filePath = "{$baseDir}/{$file}";
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimesSupported = array(
            "css" => " 	text/css",
            "png" => "image/png",
            "gif" => "image/gif",
            "jpg" => "image/jpeg",
            "js" => "application/x-javascript",
            "txt" => "text/plain",
        );

        $selectedMime = "text/plain";

        if (isset($mimesSupported[$fileExtension])) {
            $selectedMime = $mimesSupported[$fileExtension];
        }

        // Enviar el encabezado correcto.
        header("Content-Type: {$selectedMime}");

        // Enviar el contenido al navegador.
        echo file_get_contents($filePath);
    }
}