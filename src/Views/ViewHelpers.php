<?php namespace Emotion\Views;

use \League\Uri;
use \Emotion\Core;
use \Emotion\HttpContext;

class ViewHelpers {
    private $uri = null;
    /**
     * Instancia interna.
     *
     * @var \Emotion\Views\ViewHelpers
     */
    protected static $instance = null;

    public function __construct() {
        $this->uri = Uri\Http::createFromServer(HttpContext::server());
    }

    /**
     * Devuelve una instancia única de la configuración.
     *
     * @return \Emotion\Views\ViewHelpers
     */
    public static function getInstance() {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    public function registerFilters(\Twig_Environment $twigInstance) {
        $twig_content_filter = new \Twig_SimpleFilter("content", "\Emotion\Views\ViewHelpers::content");
        $twig_url_function = new \Twig_SimpleFunction("url", "\Emotion\Views\ViewHelpers::url");

        $twigInstance->addFilter($twig_content_filter);
        $twigInstance->addFunction($twig_url_function);
    }

    public static function content($fileName) {
        $instance = \Emotion\Views\ViewHelpers::getInstance();
        $config = \Emotion\Configuration\CoreConfiguration::getInstance();
        $appBasePath = Core::getRouterBase();

        if (isset($_SERVER["SERVER_SOFTWARE"])) {
            $isDevServer = strpos($_SERVER["SERVER_SOFTWARE"], "Development") !== false;            
            $host = $_SERVER["SERVER_NAME"];
            $port = $_SERVER["SERVER_PORT"] == "80" ? "" : ":" . $_SERVER["SERVER_PORT"]; 
            if ($isDevServer) {
                return "http://{$host}{$port}{$appBasePath}{$fileName}";
            }
        }
        
        $requestUri = str_replace($appBasePath, "", $_SERVER["REQUEST_URI"]);
        $uri = str_replace($requestUri, "", $instance->uri->__toString());
        return $uri . str_replace("~/", "", $fileName);
    }

    public static function url($routeName, $controllerAction = "Index", $controllerName = "", $params = "") {
        if ($params == "" && substr($controllerName, 0, 1) === "?") {
            $params = $controllerName;
            $controllerName = "";
        }

        if (substr($params, 0, 1) === "?") {
            $params = substr($params, 1);
        }

        // Crear instancia del enrutador global.
        $core = Core::getInstance();
        $config = $core->getConfig();
        $router = $core->getRouter();
        $match = $core->getRouterResults();

        // Asignar valores predeterminados.
        if ($controllerName === "") {
            if (isset($params["controllerName"])) {
                $controllerName = $match["controllerName"];
            } else {
                $controllerName = $config->controllerName;
            }
        }

        if ($controllerAction === "") {
            if (isset($params["controllerAction"])) {
                $controllerAction = $match["controllerAction"];
            } else {
                $controllerAction = $config->controllerAction;
            }
        }
        
        // Validar parametros de la url
        $options = array();
        parse_str($params, $options);
        $urlQuery = \http_build_query($options);

        // Crear opciones
        $options["controllerName"] = $controllerName;
        $options["controllerAction"] = $controllerAction;

        if ($routeName === "") {
            $routeName = "default";
        }

        return $router->generate($routeName, $options) . $urlQuery;
    }
}