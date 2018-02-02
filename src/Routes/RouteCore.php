<?php namespace Emotion\Routes;

use \Emotion\Core;
use \Emotion\Core\Bootstrapper;

class RouteCore extends Bootstrapper {
    /**
     * Enrutador
     * 
     * @var \AltoRouter
     */
    public static $router = null;

    /**
     * Devuelve la instancia del enrutador.
     *
     * @return \AltoRouter
     */
    public static function getRouter() {
        if (self::$router === null) {
            Core::log("El enrutador no ha sido inicializado.");
            // En caso de que no haya sido inicializado anteriormente.
            self::$router = new \AltoRouter();
        } else {
            Core::log("Recuperando enrutador inicializado.");
        }

        return self::$router;
    }

    public static function clearRouter() {
        Core::log("Reinicializando enrutador a un nueva instancia.");
        self::$router = new \AltoRouter();
    }
}
