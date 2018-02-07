<?php namespace Emotion\Core\Routes;

use \Emotion\Utils;
use \Emotion\Contracts\Configuration\IConfigurationRoot;
use \Emotion\Contracts\IReadOnlyAppState;

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
            $this->addStaticFolderEx("public", $rules, $routeName);
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
        $rules = "{virtualFolder}[*:publicFile]")
        {
            if ($virtualFolder === null) {
                $virtualFolder = $folderName . "/";
            }

            if ($defaultDocument != null) {
                // Cuando existe un documento "predeterminado", se debe agregar que 
                // busque en toda la carpeta primero.
                $this->addStaticFolder($folderName, null, $virtualFolder);

                // Y luego cambiar la regla para que acepte solo ese ruta.
                $rules = "{virtualFolder}/";
            }

            // Asignar el mismo nombre de la carpeta.
            $rules = str_replace("{virtualFolder}", $virtualFolder, $rules);
            $this->addStaticFolderEx($folderName, $rules, $folderName . "Rule-" . $rules, $defaultDocument);
    }

    public function addStaticFolderEx(
        $folderName,
        $routeRules,
        $routeName,
        $defaultDocument = null) {
            // Localizar la ruta principal de la aplicación.
            $root = $this->getDirectoryBase();
            
            // Ajustar la ruta para que considere bien la carpeta.
            $folderLocation = "{$root}/{$folderName}";

            // Configurar correctamente la regla.
            $rules = $this->formatRouteRule($routeRules);

            $this->logger->debug(0, "Agregando una ruta estática al ruteador. Regla: " . $rules . ", folder: " . $folderLocation);

            $this->map("GET", $rules, function($publicFile = null) use ($folderLocation, $defaultDocument) {
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
            }, $routeName);
    }
}