<?php namespace Emotion\Routes;

use \Emotion\Configuration\ConfigurationCore;

class RouteExtra extends RouteUtils {
    public static function addMvc(
        $routeName = "default",
        $rules = "[a:controllerName]?/[a:controllerAction]?/?") {
            $rules = self::formatRouteRule($rules);

            \Emotion\Core::log("Agregando una ruta MVC. Regla: " . $rules );

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

            \Emotion\Core::log("Agregando una ruta API MVC. Regla: " . $rules );

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

    /**
     * Agrega una carpeta estática al enrutador.
     *
     * @param string $folderName Nombre de la carpeta existente.
     * @param string $defaultDocument Archivo predeterminado.
     * @param string $virtualFolder Carpeta que será usada en la URL. Dejar vacía para usar la raiz de la URL.
     * @param string $rules Regla a utilizar en el enrutador.
     * @return void
     */
    public static function addStaticFolder(
        $folderName,
        $defaultDocument = null,
        $virtualFolder = null,
        $rules = "{virtualFolder}[*:publicFile]")
        {
            if ($virtualFolder === null) {
                $virtualFolder = $folderName . "/";
            }

            if ($defaultDocument != null) {
                // Asignar documento predeterminado.
                $rules .= "?/";
            }

            // Asignar el mismo nombre de la carpeta.
            $rules = str_replace("{virtualFolder}", $virtualFolder, $rules);
            self::addStaticFolderEx($folderName, $rules, $folderName . "Rule-" . $rules, $defaultDocument);
    }

    public static function addStaticFolderEx(
        $folderName,
        $routeRules,
        $routeName,
        $defaultDocument = null) {
            // Localizar la ruta principal de la aplicación.
            $root = ConfigurationCore::getSourceDirectory();
            
            // Ajustar la ruta para que considere bien la carpeta.
            $folderName = "{$root}/{$folderName}";

            // Configurar correctamente la regla.
            $rules = self::formatRouteRule($routeRules);

            \Emotion\Core::log("Agregando una ruta estática al ruteador. Regla: " . $rules . ", folder: " . $folderName);

            self::map("GET", $rules, function($publicFile = null) use ($folderName, $defaultDocument) {
                if ($publicFile == null && $defaultDocument == null) {
                    // No se permite no pasar el nombre del archivo cuando no hay un documento predeterminado.
                    throw new \Emotion\Exceptions\InternalException(
                        sprintf(ExceptionCodes::S_ROUTE_STATIC_FILE_EMPTY, $baseDir),
                        ExceptionCodes::E_ROUTE_STATIC_FILE_EMPTY
                    );
                }

                if ($publicFile == null && $defaultDocument != null) {
                    // Asignar el nombre del archivo cuando este no haya sido definido.
                    $publicFile = $defaultDocument;
                }

                self::serve($publicFile, $folderName);
            }, $routeName);
    }
}