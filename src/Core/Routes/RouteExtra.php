<?php namespace Emotion\Core\Routes;

use \Emotion\Utils;
use \Emotion\Contracts\IStaticFolderRoute;
use \Emotion\Contracts\IReadOnlyAppState;
use \Emotion\Core\StaticFolderRoute;

class RouteExtra extends RouteUtils {
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
     * Devuelve el estado de sólo lectura de la aplicación.
     *
     * @return IReadOnlyAppState
     */
    public function getReadOnlyState($app = null) {
        if ($app == null) {
            $app = $this;
        }

        return $app;
    }
    
    public function addMvc(
        $routeName = "default",
        $rules = "[a:controllerName]?/[a:controllerAction]?/?") {
            $rules = $this->formatRouteRule($rules);

            $this->logger->debug(0, "Agregando una ruta MVC. Regla: " . $rules);

            $appState = $this->getReadOnlyState($this);

            $this->map(
                'GET|POST',
                $rules, 
                function($controllerName = "Home", $controllerAction = "Index") use ($appState) {
                    $logger = new \Emotion\Loggers\Logger("mvc-run");

                    if (!($appState instanceof IReadOnlyAppState)) {
                        throw new \Exception("Se requiere una instancia de IReadOnlyAppState.");
                    }
                    
                    // Obtener la carpeta raíz de MVC.
                    $rootFolder = $appState->getConfiguration()->getValue("src");
                    $mvcFolder  = $appState->getConfiguration()->getValue("mvc");

                    // Obtener la carpeta de controladores.
                    $baseDir = Utils::combinePaths($rootFolder, $mvcFolder);

                    $logger->debug(0, "Controlador: \"{$controllerName}\".\"{$controllerAction}\".");
                    $logger->debug(0, "Directorio: {$baseDir}.");

                    // Recuperar valores predeterminados.
                    if ($controllerName === "") {
                        $logger->info(0, "Asignando controlador predeterminado.");
                        $controllerName = $appState->getConfiguration()->getValue("controllerName");
                    }

                    if ($controllerAction === "") {
                        $logger->info(0, "Asignando acción predeterminada.");
                        $controllerAction = $appState->getConfiguration()->getValue("controllerAction");
                    }

                    // Obtener el acceso al controlador.
                    $controller = new \Emotion\Controller($controllerName, $controllerAction, $baseDir);

                    // Y Ejecutarla
                    $output = $controller->run("", $appState);
                
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
                        $viewEngine = new \Emotion\ViewEngine(
                            $controllerName,
                            $controllerAction,
                            $controller->getBaseDir("views"));
                
                        $viewEngine->render($output, $viewbag);
                    } else {
                        // Ejecutarla y enviar la salida al navegador.
                        // Solo en caso de que no sea una vista.
                        $output->tryProcess();
                    }             
                },
                $routeName);
    }

    public function addMvcApi(
        $routeName = "api",
        $rules = "api/[a:controllerName]?/[a:controllerAction]?/?") {
            $rules = $this->formatRouteRule($rules);

            $this->logger->debug(0, "Agregando una ruta API MVC. Regla: " . $rules);
            $appState = $this->getReadOnlyState($this);

            $this->map( 'GET|POST', $rules, function($controllerName = "Home", $controllerAction = "Index") use ($appState) {
                if (!($appState instanceof IReadOnlyAppState)) {
                    throw new \Exception("Se requiere una instancia de IReadOnlyAppState.");
                }

                // Obtener la carpeta raíz de API.
                $rootFolder = $appState->getConfiguration()->getValue("src");
                $apiFolder = $appState->getConfiguration()->getValue("api");

                // Obtener la carpeta de controladores.
                $baseDir = Utils::combinePaths($rootFolder, $apiFolder);

                // Obtener el acceso al controlador.
                $controller = new \Emotion\Controller($controllerName, $controllerAction, $baseDir);
            
                // Ejecutarla y enviar la salida al navegador.
                $controller->run("", $appState)->tryProcess();
            }, $routeName);
    }

    public function addStaticFiles(
        $routeName = "public", 
        $rules = "public/[*:publicFile]") {
        
            // Asignar el mismo nombre de la carpeta.
            $ruleObj = new StaticFolderRoute();

            $ruleObj->setDirectory("public");
            $ruleObj->setRule($rules, "Rule-" . $routeName);
            
            $this->AddCustomStaticFolder($this->ensureDefaults($ruleObj));
    }
    
    public function AddCustomStaticFolder(IStaticFolderRoute $staticFolder) {
        $this->logger->trace(0, "Agregando carpeta estática personalizada.");
        
        if ($staticFolder == null) {
            throw new \Exception("La información de la carpeta es nula.");
        }
        
        $this->logger->debug(0, "Confirmando valores predeterminados.");
        $routeConfig = $this->ensureDefaults($staticFolder);
        
        $this->logger->debug(0, "Obteniendo reglas...");
        // Reglas que se aplicarán en el enrutador.
        $expectedRules = $routeConfig->getRules();
        $this->logger->info(0, "Hay " . count($expectedRules) . " reglas definidas.");
        
        // Obtener la referencia a la aplicación.
        $this->logger->debug(0, "Recuperando valor actual del estado de la aplicación.");
        $appState = $this->getReadOnlyState($this);
        
        // Cada regla debe agregarse al enrutador.
        $this->logger->debug(0, "Asignando reglas al enrutador.");
        foreach ($expectedRules as $routeRule) {
            $this->logger->info(0, "Regla: {$routeRule}");
            $expectedRule = $this->formatRouteRule($routeRule);            
            
            $this->map("GET", $expectedRule, function($publicFile = null) use ($routeConfig, $appState) {
                $logger = new \Emotion\Loggers\Logger("static:map:1");
                
                // Definir el archivo que se esta solicitando por la regla.
                $routeConfig->setRequestFileName($publicFile);
                
                // Referenciar al estado de la aplicación.
                $routeConfig->setReadOnlyAppState($appState);
                
                // Eliminar el callback para que no sea llamado nuevamente.
                $c = $routeConfig->getCallback();
                
                $logger->info(0, "Llamando callback: {$publicFile}");

                // Enlazar 
                $c2 = \Closure::bind($c, $routeConfig);
                
                // Ejecutar la función.
                $c2();
            });
        }
        
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
    public function addStaticFolder(
        $folderName,
        $defaultDocument = null,
        $virtualFolder = null,
        $rules = "{virtualFolder}[*:publicFile]") {
            // Crear la regla.
            $staticFolder = $this->getStaticFolderInstance($folderName, $defaultDocument, $virtualFolder, $rules);
            
            // Agregar al sistema.
            $this->AddCustomStaticFolder($staticFolder);
    }
    
    /**
     * 
     * @param type $folderName
     * @param type $defaultDocument
     * @param string $virtualFolder
     * @param type $rules
     * @throws \Exception
     * @return \Emotion\Contracts\IStaticFolderRoute
     */
    private function getStaticFolderInstance(
        $folderName,
        $defaultDocument = null,
        $virtualFolder = null,
        $rules = "{virtualFolder}[*:publicFile]",
        $callback = null)
        {
            if ($rules == "") {
                throw new \Exception("Debe indicar una regla.");
            }
            
            // Asignar el mismo nombre de la carpeta.
            $ruleObj = new StaticFolderRoute();
            
            $ruleObj->setDefaultDocument($defaultDocument);
            $ruleObj->setDirectory($folderName);
            $ruleObj->setRule($rules, "Rule-" . $rules);
            $ruleObj->setVirtualDirectory($virtualFolder);
            $ruleObj->setCallback($callback);

            return $this->ensureDefaults($ruleObj);
    }
    
    /**
     * Devuelve un objeto con los valores predeterminados.
     * 
     * @param IStaticFolderRoute $staticFolder
     * @return IStaticFolderRoute
     */
    public function ensureDefaults(IStaticFolderRoute $staticFolder) {
        $this->logger->trace(0, "Validando configuraciones predeterminadas.");
        $callback = $staticFolder->getCallback();
        
        if ($callback == null) {
            $this->logger->debug(0, "Creando función predeterminada.");
            $callback = $this->getDefaultStaticCallbackBehavior();
            
            $this->logger->debug(0, "Asignando función predeterminada.");
            $staticFolder->setCallback($callback);
        } else {
            $this->logger->info(0, "La confirmación tiene su propia función de retorno.");
        }

        return $staticFolder;
    }
    
    public function getDefaultStaticCallbackBehavior() 
    {
        return function() {
            $logger = new \Emotion\Loggers\Logger("defaultCBeh");
            
            $logger->debug(0, "Ejecutando comportamiento predeterminado para carpetas estáticas.");
            // Para obtener las sugerencias del editor, paso $this por esta función.
            // Así el editor sabe que se refiere a un IStaticFolderRoute.
            // No es necesario en tiempo de ejecución.
            $thisObj = Utils::getAsStaticFolderRoute($this);
            
            // Conformo la ruta de acceso a la carpeta base.
            $rootDirectory = $thisObj->getReadOnlyAppState()->getDirectoryBase();
            $folderLocation = $rootDirectory . $thisObj->getDirectory();

            // Asigno la información de la ruta estática.
            $defaultDocument = $thisObj->getDefaultDocument();
            $publicFile = $thisObj->getRequestFileName();
            
            $logger->info("Root: ", $rootDirectory);
            $logger->info("folderLocation: ", $folderLocation);
            $logger->info("defaultDocument: ", $defaultDocument);
            $logger->info("publicFile: ", $publicFile);

            if ($publicFile == null && $defaultDocument == null) {
                // No se permite no pasar el nombre del archivo cuando no hay un documento predeterminado.
                throw new \Emotion\Exceptions\InternalException(
                    sprintf(ExceptionCodes::S_ROUTE_STATIC_FILE_EMPTY, $folderLocation),
                    ExceptionCodes::E_ROUTE_STATIC_FILE_EMPTY
                );
            }

            if ($publicFile == null && $defaultDocument != null) {
                // Asignar el nombre del archivo cuando este no haya sido definido.
                $publicFile = $defaultDocument;
            }

            RouteUtils::serve($publicFile, $folderLocation);
        };
    }
}