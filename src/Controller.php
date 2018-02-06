<?php namespace Emotion;

use \Emotion\Exceptions\ExceptionCodes;
use \Emotion\Contracts\Configuration\IConfigurationRoot;
use \Emotion\Contracts\IReadOnlyAppState;

class Controller {
    private $_config;
    private $_name = "";
    private $appFolder = "";
    private $action = "index";
    private $baseFolder = "";
    private $controllerFolder = "";

    /**
     * Registro de eventos.
     *
     * @var \Emotion\Contracts\ILogger
     */
    private $logger = null;

    /**
     * @var \Emotion\IControllerBase
     */
    private $controllerInstance = null;
    
    public function __construct(
        $controllerName,
        $controllerAction = "Index",
        $baseFolder = "") {        
        $this->_name = $controllerName;
        $this->action = $controllerAction;
        $this->baseFolder = $baseFolder;

        // Crear registro de eventos.
        $this->logger = new \Emotion\Loggers\Logger(self::class);
        $this->logger->debug(0, "Controller: {$controllerName}.{$controllerAction} ({$baseFolder})");
    }

    public function getControllerName() {
        return $this->_name;
    }

    public function getControllerAction() {
        return $this->action;
    }

    public function init(IConfigurationRoot $configuration = null) {
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
        $this->controllerInstance = new $controllerClassName($configuration);
    }

    public function run($actionName = "", IReadOnlyAppState $appState = null) {
        if ($actionName === "") {
            $actionName = $this->action;
        }

        try {
            $this->init($appState->getConfiguration());

            $this->logger->debug(0, "Ejecutando la acción {$actionName}");
            $output = $this->controllerInstance->run($actionName);
            
            if (is_array($output)) {
                $this->logger->debug(0, "La respuesta es un arreglo y será convertido a JsonResponse");
                $output = new \Emotion\Responses\JsonResponse($output);
            }
            
            // En caso de no ser una instancia de BaseResponse la 
            // inicializo como una de ellas.
            if (!($output instanceof \Emotion\Responses\BaseResponse)) {
                $this->logger->warn(0, "La respuesta es desconocida, se tratará como HtmlResponse.");
                $output = new \Emotion\Responses\RawResponse($output);
            }
        } catch (\Emotion\Exceptions\AuthException $ex) {
            $this->logger->error(0, $ex);
            $output = new \Emotion\Responses\ErrorResponse(401, "Unauthorized", $ex);
        } catch (\Emotion\Exceptions\NotFoundException $ex) {
            $this->logger->error(0, $ex);
            $output = new \Emotion\Responses\ErrorResponse(404, "Not Found", $ex);
        } catch (\Emotion\Exceptions\InternalException $ex) {
            $this->logger->error(0, $ex);
            $output = new \Emotion\Responses\ErrorResponse(500, "Server Error", $ex);
        } catch (\Emotion\Exceptions\RedirectException $ex) {
            $this->logger->error(0, $ex);
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