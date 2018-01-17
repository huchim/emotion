<?php namespace Emotion;

class Routes {
    public static function get($route, $callback) {
        $config = Configuration\CoreConfiguration::getInstance()->getConfig();
        $controllerName = trim($_GET['md']);
        $controllerAction = trim($_GET['op']);

        if ($controllerName === "") {
            $controllerName = $config->controllerName;
        }

        if ($controllerAction === "") {
            $controllerAction = $config->controllerAction;
        }

        $request = new Routes\Request($controllerName, $controllerAction, $_GET, $_POST);
        $response = new CodeInjector();

        // Ejecutar tarea.
        $callback($request, $response);
    }
}