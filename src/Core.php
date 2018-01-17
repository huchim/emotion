<?php namespace Emotion;

class Core {
    /**
     * Configuración
     *
     * @var \Emotion\Configuration\CoreConfiguration
     */
    private $configuration = null;

    /**
     * Enrutador.
     *
     * @var \AltoRouter
     */
    private $router = null;
    protected static $instance = null;
    private $routeResult = null;

    public $info = array();

    private function __construct() {
        // Crear configuración inicial.
        $this->router = new \AltoRouter();
        $this->init();
    }

    public function init() {
        $this->configuration = Configuration\CoreConfiguration::getInstance();

        if ($this->configuration->getBasePath() !== "") {
            $this->router->setBasePath($this->configuration->getBasePath());
        }

        $this->info = [];

        if (file_exists("package.json")) {
            $this->info = json_decode(\file_get_contents("package.json"), true);
        }

        if (file_exists("app.json")) {
            $this->info = array_merge($this->info, json_decode(\file_get_contents("app.json"), true));
        }
    }

    public static function loadConfig($fileName) {
        // Actualiza la configuración.
        $self = Core::getInstance();
        $self->configuration->loadConfig($fileName);
        $self->init();
    }

    public static function info() {
        return Core::getInstance()->info;
    }

    public static function get($option) {
        $self = Core::getInstance();

        if (isset($self->info[$option])) {
            return $self->info[$option];
        }

        return "";
    }

    public static function connectionStrings($connectionName) {
        $connections = Core::get("connectionStrings");

        if (!is_array($connections)) {
            throw new \Exception("No se pudo encontrar la sección de conexiones en app.json");
        }

        if (!isset($connections[$connectionName])) {
            throw new \Exception("No se puede encontra la cadena de conexión con el nombre {$connecctionName}");
        }

        $connectionParts = explode(";", $connections[$connectionName]);
        $connectionOptions = [];

        foreach ($connectionParts as $connectionOption) {
            $options = explode("=", $connectionOption);
            $optionName = strtolower($options[0]);
            $optionValue = $options[1];
            $connectionOptions[$optionName] = $optionValue;
        }
        
        return $connectionOptions;
    }

    public static function log($message) {
        // TODO: Deshabilitar cuando no sea necesario.
        file_put_contents('php://stderr', $message . "\n");
    }

    /**
     * Devuelve el encargado de administrar la sesión del usuario.
     *
     * @return \Emotion\Security\ICredentialRepository
     */
    public static function getCredentialRepository() {
        return new \Emotion\Security\CookieUnSecure();
    }

    public static function addMvc($routeName = "default", $rules = "[a:controllerName]?/[a:controllerAction]?/?") {
        Core::map( 'GET|POST', $rules, function($controllerName = "Home", $controllerAction = "Index") {
            // Obtener el acceso al controlador.
            $controller = new \Emotion\Controller($controllerName, $controllerAction);
        
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
                $output->process();
            }  
        
        }, $routeName);
    }

    public static function addMvcApi($routeName = "api", $rules = "api/[a:controllerName]?/[a:controllerAction]?/?") {
        Core::map( 'GET|POST', $rules, function($controllerName = "Home", $controllerAction = "Index") {
            $apiFolder = Core::getInstance()->getConfig()->api;
            
            // Obtener el acceso al controlador.
            $controller = new \Emotion\Controller($controllerName, $controllerAction, $apiFolder);
        
            // Ejecutarla y enviar la salida al navegador.
            $controller->run()->process();
        }, $routeName);
    }

    public static function addStaticFiles($routeName = "public", $rules = "public/[*:publicFile]") {
        Core::map( "GET", $rules, function($publicFile) {
            Core::serve($publicFile);
        }, $routeName);
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

    /**
     * Devuelve una instancia única de la configuración.
     *
     * @return Core
     */
    public static function getInstance() {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Obtener la configuración del sitio.
     *
     * @return \Emotion\Configuration\DirectoryConfiguration
     */
    public function getConfig() {
        return $this->configuration->getConfig();
    }

    public function setRouterResults($routeResult) {
        $this->routeResult = $routeResult;
    }

    public function getRouterResults() {
        return $this->routeResult;
    }

    public static function run() {
        $self = Core::getInstance();

        // Analizar posibles resultados.
        $router = $self->getRouter();

        // Si no se ha configurado ninguna ruta, agrego las predeterminadas:
        if (count($router->getRoutes()) === 0) {
            Core::addStaticFiles();
            Core::addMvcApi();
            Core::addMvc();
        }

        $match = $router->match();

        // Definer resultado activo del enrutador.
        $self->setRouterResults($match["params"]);

        // Ejecutar la ruta o devolver un error 404.
        if( $match && is_callable( $match['target'] ) ) {
            $c = call_user_func_array( $match['target'], $match['params'] ); 
        } else {
            // no route was matched
            header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
        }
    }

    /**
     * devuelve la instancia del enrutador.
     *
     * @return \AltoRouter
     */
    public function getRouter() {
        return $this->router;
    }

    public static function setBasePath($basePath) {
        $router = Core::getInstance()->getRouter();
        $router->setBasePath($basePath);
    }

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
        $router = Core::getInstance()->getRouter();
		$router->map($method, $route, $target, $name);
	}
}