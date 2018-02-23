<?php namespace Emotion;

use \Emotion\Core\Bootstrapper;
use \Emotion\Contracts\Configuration\IConfigurationRoot;

class ControllerBase implements IControllerBase {

    /**
     * Undocumented variable
     *
     * @var \Emotion\AppUser
     */
    private $appUser = null;

    /**
     * Registro de eventos.
     *
     * @var \Emotion\Contracts\ILogger
     */
    private $logger = null;

    /**
     * Infomración que será pasada a la vista.
     *
     * @var \Emotion\Views\ViewBag
     */
    public $ViewBag = null;
    private $currentControllerAction = "Index";

    /**
     * Undocumented variable
     *
     * @var \Emotion\Contracts\Configuration\IConfigurationRoot
     */
    private $configuration = null;

    public function __construct(IConfigurationRoot $configuration = null) {
        $this->logger = new \Emotion\Loggers\Logger(self::class);
        $this->configuration = $configuration;

        // Leer del repositorio el usuario actual.
        $this->appUser = $repo = Bootstrapper::getCredentialRepository()->readUser();
        $authenticated = $this->appUser->isLogged();

        $this->ViewBag = new \Emotion\Views\ViewBag(array(
            // "app" => Core::info(), 
            "authenticated" => $authenticated, 
            "currentUser" => $this->appUser,
        ));
    }

    /**
     * Undocumented function
     *
     * @return use \Emotion\Contracts\Configuration\IConfigurationRoot
     */
    protected function getConfiguration() {
        return $this->configuration;
    }

    public function assign($key, $value) {
        $this->ViewBag[$key] = $value;
    }

    public function run($actionName) {
        $this->logger->debug(0, "Este es el controlador que ejecutará {$actionName}");
        $this->currentControllerAction = $actionName;

        // Recuperar la lista de parámetros
        try {
            $method = new \ReflectionMethod(\get_class($this), $actionName);
        }
        catch (\Exception $ex) {
            $this->logger->error(0, $ex);
            throw $ex;
        }        

        $params = $method->getParameters();
        $paramValues = array();
        $paramsOptions = array();

        foreach ($params as $param) {
            $paramValues[$param->name] = $param->isOptional() ? $param->getDefaultValue() : null;
            $paramsOptions[$param->name] = $param->isOptional();
        }

        // Si el método es POST, puedo crear el payload.
        $createPayload = $this->getRequestMethod() === "POST";
        
        if ($createPayload) {
            $payload = \Emotion\HttpContext::post();
        }
        
        foreach ($paramValues as $key => $value) {
            $found = false;
            $paramFromGetArray = \Emotion\HttpContext::get($key);
            $paramFromPostArray = \Emotion\HttpContext::post($key);

            if ($paramFromGetArray !== null) {
                $isJson = substr($paramFromGetArray, 0, 1) === "{";

                if ($isJson) {
                    $paramFromGetArray = json_decode($paramFromGetArray, true);
                }

                $paramValues[$key] = $paramFromGetArray;
                $found = true;
            }

            // las variables de POST tienen prioridad sobre GET.
            if ($paramFromPostArray !== null) {
                $isJson = substr($paramFromPostArray, 0, 1) === "{";

                if ($isJson) {
                    $paramFromPostArray = json_decode($paramFromPostArray, true);
                }

                $paramValues[$key] = $paramFromPostArray;
                $found = true;
            }
            
            if (strtolower($key) == "payload" && $createPayload) {
                // Asignar la variable "payload" desde el contenido de FORM.
                $paramValues[$key] = $payload;
                $found = true;
            }

            if (!$found && $paramsOptions[$key] === false) {
                // Si se requiere y no fue encontrado lanzar error.
                throw new \Emotion\Exceptions\InternalException("Se requiere el argumento {$key} para poder continuar.");
            }
        }

        $output = null;

        if (count($paramValues) === 0) {
            $this->logger->debug(0, "La solicitud no contiene parámetros. Se va a ejecutar {$actionName}");
            $output = $this->$actionName();
        } else {
            $this->logger->debug(0, "La solicitud se ejecutará con " . count($paramValues) . " parámetros.");
            $output = call_user_func_array(array($this, $actionName), $paramValues);
        }
    
        $this->logger->debug(0, "Ha finalizado la ejecución del controlador.");
        if ($output === null) {
            $this->logger->warn(0, "El resultado es nulo, verifique que contiene la instrucción 'return \$this->View()'.");
            return new \Emotion\Responses\NoContentResponse();
        } else {
            return $output;
        }
    }

    public function getOptions() {
        return $this->ViewBag;
    }

    public function getClaim($claimValue) {
        return $this->appUser->getClaim($claimValue);
    }

    public function View($viewName = "", $model = array()) {
        if ($viewName === "") {
            $viewName = $this->currentControllerAction;
        }

        // Asignar el modelo a este controlador.
        $this->assign("model", $model);

        // Devolver la vista
        return new \Emotion\Responses\ViewResponse($this->getControllerName(), $viewName, (array)$this->ViewBag);
    }

    public function getControllerName() {
        return str_replace("Controller", "", get_class($this));
    }

    public function Redirect($url) {
        $this->logger->debug(0, "Redirect:{$url}");
        return new \Emotion\Responses\RedirectResponse($url);
    }

    public function RedirectToAction($controllerAction = "Index", $controllerName = "", $params = "") {
        $this->logger->debug(0, "RedirectToAction:{$controllerName}.{$controllerAction}");
        $url = \Emotion\Views\ViewHelpers::url("default", $controllerAction, $controllerName, $params);
        return $this->Redirect($url);
    }

    public function RedirectToRoute($routeName, $controllerAction = "Index", $controllerName = "", $params = "") {
        $this->logger->debug(0, "RedirectToRoute:{$routeName}{$controllerName}.{$controllerAction}");
        $url = \Emotion\Views\ViewHelpers::url($routeName, $controllerAction, $controllerName, $params);
        return $this->Redirect($url);
    }

    /**
     * Undocumented function
     *
     * @return \Emotion\AppUser
     */
    public function getCredentials() {
        return $this->appUser;
    }

    public function authorize($roleName) {
        if (!$this->isLogged()) {
            return false;
        }
        
        $withRoles = $this->appUser->getClaim("group");

        if (!is_array($roleName)) {
            $roles = split(",", $roleName);
        }

        if (!is_array($withRoles)) {
            $withRoles = split(",", $withRoles);
        }

        foreach ($roles as $role) {
            if (in_array($role, $withRoles)) {
                return true;
            }
        }

        return false;
    }

    public function notPublic($controllerName = "Account", $controllerAction = "Login", $params = "?") {
        if (!$this->isLogged()) {            
            $nextUrl = \Emotion\Views\ViewHelpers::url("default", 
                $this->currentControllerAction, 
                str_replace("Controller", "", get_class($this)), 
                $params);
            $loginUrl = \Emotion\Views\ViewHelpers::url("default", $controllerAction, $controllerName, "?redirectUrl=" . $nextUrl);
            throw new \Emotion\RedirectException($loginUrl);
        }
    }

    /**
     * Hace disponible la función únicamente en este método.
     *
     * @param string $method Lista separados por | de los métodos disponibles.
     * @return void
     */
    public function method($method) {
        $methods = explode("|", $method);
        $requestMethod = $this->getRequestMethod("GET");

        foreach ($methods as $methodName) {
            if ($methodName === $requestMethod) {
                // Este método está permitido para ejecutarse.
                return;
            }
        }

        $controllerName = $this->getControllerName();
        $controllerAction = $this->currentControllerAction;
        throw new \Emotion\NotFoundException(
            "El método {$requestMethod} no está permitido en este contexto: {$controllerName}.{$controllerAction}. " . 
            "Actualice la función <code>\$this->method(\"{$method}\")</code> incluyendo el método no permitido."
        );
    }

    public function getRequestMethod($default = "GET") {
        return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : $default;
    }

    public function isLogged() {
        return $this->appUser->isLogged();
    }
}