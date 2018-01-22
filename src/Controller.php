<?php namespace Emotion;

use \Emotion\Exceptions\ExceptionCodes;
use \Emotion\Configuration\ConfigurationCore;

class Controller {
    private $_config;
    private $_name = "";
    private $appFolder = "";
    private $action = "index";
    private $baseFolder = "";
    private $controllerFolder = "";

    /**
     * @var \Emotion\IControllerBase
     */
    private $controllerInstance = null;
    
    public function __construct(
        $controllerName,
        $controllerAction = "Index",
        $baseFolder = "") {
        // Recuperar la estructura de directorios de la aplicación.
        $this->_config = ConfigurationCore::getInstance()->getConfig();

        if ($baseFolder === "") {
            // Si no se pasa explicitamente un dirctorio raiz, se aplica el global.
            $baseFolder = ConfigurationCore::getSourceDirectory();
        }

        if ($controllerName === "") {
            $controllerName = $this->_config->controllerName;
        }

        if ($controllerAction === "") {
            $controllerAction = $this->_config->controllerAction;
        }
        
        $this->_name = $controllerName;
        $this->action = $controllerAction;
        $this->baseFolder = $baseFolder;
    }

    public function getControllerName() {
        return $this->_name;
    }

    public function getControllerAction() {
        return $this->action;
    }

    public function init() {
        if ($this->controllerInstance !== null) {
            throw new \Exception("La instancia de este controlador ha sido inicializada.");
        }

        $controllerClassFile = $this->getClassFile();

        if ($controllerClassFile === "") {
            throw new \Emotion\Exceptions\NotFoundException(
                sprintf(ExceptionCodes::S_CONTROLLER_CLASS_NOT_FOUND, $this->_name, $this->getBaseDir()),
                ExceptionCodes::E_CONTROLLER_CLASS_NOT_FOUND
            );
        }

        $codeInjector = new \Emotion\CodeInjector();
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
                $output = new \Emotion\Responses\RawResponse($output);
            }
        } catch (\Emotion\Exceptions\AuthException $ex) {
            Core::log("El controlador arrojó un error de tipo AuthException.");
            $output = new \Emotion\Responses\ErrorResponse(401, "Unauthorized", $ex);
        } catch (\Emotion\Exceptions\NotFoundException $ex) {
            Core::log("El controlador arrojó un error de tipo NotFoundException.");
            $output = new \Emotion\Responses\ErrorResponse(404, "Not Found", $ex);
        } catch (\Emotion\Exceptions\InternalException $ex) {
            Core::log("El controlador arrojó un error de tipo InternalException.");
            $output = new \Emotion\Responses\ErrorResponse(500, "Server Error", $ex);
        } catch (\Emotion\Exceptions\RedirectException $ex) {
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
        return $this->baseFolder . "/" . $suffix;
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
}