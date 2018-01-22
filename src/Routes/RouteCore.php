<?php namespace Emotion\Routes;

class RouteCore {
    /**
     * Enrutador
     * 
     * @var \AltoRouter
     */
    public static $router = null;

    protected function __construct() {
        self::$router = new \AltoRouter();
    }

    /**
     * Devuelve la instancia del enrutador.
     *
     * @return \AltoRouter
     */
    public static function getRouter() {
        if (self::$router === null) {
            // En caso de que no haya sido inicializado anteriormente.
            self::$router = new \AltoRouter();
        }

        return self::$router;
    }
}
