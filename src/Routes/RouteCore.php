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
        return self::$router;
    }
}
