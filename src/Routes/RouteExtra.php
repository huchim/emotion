<?php namespace Emotion\Routes;

use \Emotion\Configuration\ConfigurationCore;

class RouteExtra extends RouteUtils {
    public static function addMvc(
        $routeName = "default",
        $rules = "[a:controllerName]?/[a:controllerAction]?/?") {
            $rules = self::formatRouteRule($rules);

            self::map( 'GET|POST', $rules, function($controllerName = "Home", $controllerAction = "Index") {
                $subDir = ConfigurationCore::getInstance()->getConfig()->app;
                $baseDir = ConfigurationCore::getSourceDirectory($subDir);

                // Obtener el acceso al controlador.
                $controller = new \Emotion\Controller($controllerName, $controllerAction, $baseDir);

                // Y Ejecutarla    
                $output = $controller->run();
            
                // Recuperar la lista de variables del controlador.
                $viewbag = (array)$controller->getViewBag();
                
                // Si devuelve una cadena de texto, la convierto en una vista y
                // el contenido lo transfiero a la nueva instancia.
                if ($output instanceof \Emotion\Responses\HtmlResponse) {
                    $output = new \Emotion\Responses\ViewResponse($controllerName, $controllerAction, $viewbag);
                    $output->content = $output->content;
                }
                
                // Solo la proceso si el resultado es una vista.
                if ($output instanceof \Emotion\Responses\ViewResponse) {
                    // El Controlador pudo haber cambiado el nombre de la vista, así que no debo asumir que es
                    // el mismo de la solicitud.
                    $controllerAction = $output->getViewName();

                    // Envio el resultado a 
                    $viewEngine = new \Emotion\ViewEngine($controllerName, $controllerAction, $controller->getBaseDir("views"));
            
                    $viewEngine->render($output, $viewbag);
                } else {
                    // Ejecutarla y enviar la salida al navegador.
                    // Solo en caso de que no sea una vista.
                    $output->tryProcess();
                }  
            
            }, $routeName);
    }

    public static function addMvcApi(
        $routeName = "api",
        $rules = "api/[a:controllerName]?/[a:controllerAction]?/?") {
            $rules = self::formatRouteRule($rules);

            self::map( 'GET|POST', $rules, function($controllerName = "Home", $controllerAction = "Index") {
                // Recuperar el directorio de las API
                $subDir = ConfigurationCore::getInstance()->getConfig()->api;
                $baseDir = ConfigurationCore::getSourceDirectory($subDir);

                // Obtener el acceso al controlador.
                $controller = new \Emotion\Controller($controllerName, $controllerAction, $baseDir);
            
                // Ejecutarla y enviar la salida al navegador.
                $controller->run()->tryProcess();
            }, $routeName);
    }

    public static function addStaticFiles(
        $routeName = "public", 
        $rules = "public/[*:publicFile]") {
            self::addStaticFolderEx("public", $rules, $routeName);
    }

    public static function addStaticFolder(
        $folderName,
        $virtualFolder = "",
        $rules = "{virtualFolder}/[*:publicFile]")
        {
            if ($virtualFolder === "") {
                $virtualFolder = $folderName;
            }   

            // Asignar el mismo nombre de la carpeta.
            $rules = str_replace("{virtualFolder}", $virtualFolder, $rules);
            self::addStaticFolderEx($folderName, $rules, $folderName . "Rule");
    }

    public static function addStaticFolderEx(
        $folderName,
        $routeRules,
        $routeName) {
            // Localizar la ruta principal de la aplicación.
            $root = ConfigurationCore::getSourceDirectory();
            
            // Ajustar la ruta para que considere bien la carpeta.
            $folderName = "{$root}/{$folderName}";

            // Configurar correctamente la regla.
            $rules = self::formatRouteRule($routeRules);
            self::map( "GET", $rules, function($publicFile) use ($folderName) {
                self::serve($publicFile, $folderName);
            }, $routeName);
    }
}