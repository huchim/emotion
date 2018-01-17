<?php namespace Emotion;

class Controller {
    private $_config;
    private $_internal = false;
    private $_name = "";
    private $internalDir = "";
    private $appFolder = "";
    private $action = "index";

    /**
     * @var \Emotion\IControllerBase
     */
    private $controllerInstance = null;
    
    public function __construct($controllerName = "", $action = "index", $baseFolder = "") {
        $this->_name = $controllerName;
        $this->action = $action;
        $this->_config = Configuration\CoreConfiguration::getInstance()->getConfig();
        $this->internalDir = $this->_config->helper;
        $this->appFolder = $baseFolder === "" ? $this->_config->app : $baseFolder;
        $this->_internal = $this->isInternal();        
    }

    public function getControllerName() {
        return $this->_name;
    }

    public function getControllerAction() {
        return $this->action;
    }

    public function getControllerPart($partName = "INDEX") {
        if ($partName === "") {
            $partName = "INDEX";
        }

        if (strpos($partName, "_") !== true) {
            $partName .= "_PHP";
        }

        return $this->getPart(str_replace("_", ".", strtolower($partName)));
    }

    public function getHeaderController() {
        return $this->getControllerPart("HEAD");
    }

    public function getApiController() {
        return $this->getControllerPart("API");
    }

    public function getViewController() {
        return $this->getControllerPart("INDEX");
    }    

    public function init() {
        if ($this->controllerInstance !== null) {
            throw new \Exception("La instancia de este controlador ha sido inicializada.");
        }

        $codeInjector = new \Emotion\CodeInjector();
        $controllerClassFile = $this->getClassFile();

        if ($controllerClassFile === "") {
            throw new \Emotion\NotFoundException("No se pudo localizar el archivo que contiene el controlador {$this->_name}");
        }

        $codeInjector->tryToAdd($controllerClassFile, true, true);
        
        // Crear la instancia a la clase.
        $controllerClassName = $this->getClassName();
        $this->controllerInstance = new $controllerClassName();
    }

    public function run($actionName = "") {
        if ($actionName === "") {
            $actionName = $this->action;
        }

        try {
            $this->init();

            Core::log("Ejecutando la acción {$actionName}");
            $output = $this->controllerInstance->run($actionName);
            
            if (is_array($output)) {
                Core::log("La respuesta es un arreglo y será convertido a JsonResponse");
                $output = new \Emotion\Responses\JsonResponse($output);
            }
            
            // En caso de no ser una instancia de BaseResponse la 
            // inicializo como una de ellas.
            if (!($output instanceof \Emotion\Responses\BaseResponse)) {
                Core::log("La respuesta es desconocida, se tratará como HtmlResponse.");
                $output = new \Emotion\Responses\HtmlResponse($output);
            }
        } catch (\Emotion\AuthException $ex) {
            Core::log("El controlador arrojó un error de tipo AuthException.");
            $output = new \Emotion\Responses\ErrorResponse(401, "Unauthorized", $ex);
        } catch (\Emotion\NotFoundException $ex) {
            Core::log("El controlador arrojó un error de tipo NotFoundException.");
            $output = new \Emotion\Responses\ErrorResponse(404, "Not Found", $ex);
        } catch (\Emotion\InternalException $ex) {
            Core::log("El controlador arrojó un error de tipo InternalException.");
            $output = new \Emotion\Responses\ErrorResponse(500, "Server Error", $ex);
        } catch (\Emotion\RedirectException $ex) {
            Core::log("El controlador arrojó un error de tipo RedirectException.");
            $output = new \Emotion\Responses\RedirectResponse($ex->getUrl());
        }
        
        return $output;
    }

    public function getViewBag() {
        if ($this->controllerInstance === null) {
            return array();
        }

        return $this->controllerInstance->getOptions();
    }

    public function getBaseDir($suffix = "controllers") {
        return ($this->_internal ? $this->internalDir : $this->appFolder) . "/" . $suffix;
    }

    private function getClassName() {
        $controllerName = $this->_name;
        $controllerClass = "{$controllerName}Controller";
        $controllerClassTest  = ucfirst($controllerClass);

        $controllerClassNames = array(
            "{$controllerClassTest}",
            "{$controllerClass}",        
        );

        foreach ($controllerClassNames as $className) {
            if (class_exists($className, false)) {
                return $className;
            }
        }
    }

    private function getClassFile() {
        $controllerBaseFolder = $this->getBaseDir();
        $controllerName = $this->_name;
        $controllerClass = "{$controllerName}Controller.php";
        $controllerClassTest  = ucfirst($controllerClass);

        $controllerPaths = array(
            "{$controllerBaseFolder}/{$controllerName}/{$controllerClassTest}",
            "{$controllerBaseFolder}/{$controllerName}/{$controllerClass}",            
            "{$controllerBaseFolder}/{$controllerClassTest}",
            "{$controllerBaseFolder}/{$controllerClass}",            
        );

        foreach ($controllerPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return ""; 
    }

    private function getPart($part) {
        $controllerBaseFolder = ($this->_internal ? $this->internalDir : $this->appFolder);
        $filePart = "{$controllerBaseFolder}/{$this->_name}/{$part}";

        if ($part === "CLASS") {
            return $this->getClassFile();
        }

        if (!file_exists($filePart)) {
            return "";
        }

        return $filePart;
    }

    private function exists($controllerPart) {
        return $this->existsEx($this->_name, $controllerPart, $this->internalDir, $this->appFolder);
    }

    private function isInternal()
    {
        return $this->isInternalEx($this->_name, $this->internalDir, $this->appFolder);
    }

    private function isInternalEx($controllerName, $internalFolder, $appFolder)
    {
        if (file_exists("{$appFolder}/controllers/{$controllerName}/")) {
            return false;
        }

        if (file_exists("{$appFolder}/controllers/{$controllerName}.php")) {
            return false;
        }

        if (file_exists("{$internalFolder}/controllers/{$controllerName}/")) {
            return true;
        }

        if (file_exists("{$internalFolder}/controllers/{$controllerName}.php")) {
            return true;
        }

        return false;
    }

    private function existsEx($controllerName, $controllerPart, $internalFolder, $appFolder) {
        // app/controllers/home/head.php
        if (file_exists("{$appFolder}/controllers/{$controllerName}/{$controllerPart}")) {
            return true;
        }

        if (file_exists("{$nternalFolder}/controllers/{$controllerName}/{$controllerPart}")) {
            return true;
        }

        return false;
    }
}