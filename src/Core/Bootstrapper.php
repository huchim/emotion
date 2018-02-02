<?php namespace Emotion\Core;

class Bootstrapper extends Configuration {
    public static function log($message) {
        // TODO: Deshabilitar cuando no sea necesario.
        $config = self::getConfigurationObject();

        if ($config->isDebug()) {
            // throw new \Exception("No se permite debug en pruebas");
            file_put_contents('php://stderr', $message . "\n");
        }
    }
}