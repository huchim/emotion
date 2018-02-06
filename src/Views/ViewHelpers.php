<?php namespace Emotion\Views;

use \League\Uri;
use \Emotion\HttpContext;
use \Emotion\Contracts\Configuration\IConfigurationRoot;
use \Emotion\Contracts\IReadOnlyAppState;

class ViewHelpers {
    private $uri = null;

    /**
     * Undocumented variable
     *
     * @var \Emotion\Contracts\IReadOnlyAppState
     */
    private $appState = null;

    /**
     * Instancia interna.
     *
     * @var \Emotion\Views\ViewHelpers
     */
    protected static $instance = null;

    public function __construct(IReadOnlyAppState $appState) {
        $this->uri = Uri\Http::createFromServer(HttpContext::server());
        $this->appState = $appState;
    }

    public function registerFilters(\Twig_Environment $twigInstance) {
        $twig_content_filter = new \Twig_SimpleFilter("content", array($this, "content2"));
        $twig_url_function = new \Twig_SimpleFunction("url", array($this, "url"));

        $twigInstance->addFilter($twig_content_filter);
        $twigInstance->addFunction($twig_url_function);
    }

    public function content2($fileName) {
        $appBasePath = $this->appState->getConfiguration()->getValue("RouteUrlBase");
        
        if (isset($_SERVER["SERVER_SOFTWARE"])) {
            $isDevServer = strpos($_SERVER["SERVER_SOFTWARE"], "Development") !== false;            
            $host = $_SERVER["SERVER_NAME"];
            $port = $_SERVER["SERVER_PORT"] == "80" ? "" : ":" . $_SERVER["SERVER_PORT"]; 
            if ($isDevServer) {
                return "http://{$host}{$port}/{$appBasePath}{$fileName}";
            }
        }
        
        $requestUri = str_replace($appBasePath, "", HttpContext::server("REQUEST_URI"));
        $uri = str_replace($requestUri, "", $this->uri->__toString());
        return $uri . "/" . str_replace("~/", "", $fileName);
    }

    public function url($routeName, $controllerAction = "Index", $controllerName = "", $params = "") {
        if ($params == "" && substr($controllerName, 0, 1) === "?") {
            $params = $controllerName;
            $controllerName = "";
        }

        if (substr($params, 0, 1) === "?") {
            $params = substr($params, 1);
        }

        // Crear instancia del enrutador global.
        $config = $this->appState->getConfiguration();
        $router = $this->appState->getRouter();
        $match  = $this->appState->getRouterResults();

        // Asignar valores predeterminados.
        if ($controllerName === "") {
            if (isset($params["controllerName"])) {
                $controllerName = $match["controllerName"];
            } else {
                $controllerName = $config->getValue("controllerName");
            }
        }

        if ($controllerAction === "") {
            if (isset($params["controllerAction"])) {
                $controllerAction = $match["controllerAction"];
            } else {
                $controllerAction = $config->getValue("controllerAction");
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