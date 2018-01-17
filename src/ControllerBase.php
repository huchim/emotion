<?php namespace Emotion;

use \Emotion\Core as Core;

class ControllerBase implements IControllerBase {

    /**
     * Undocumented variable
     *
     * @var \Emotion\AppUser
     */
    private $appUser = null;

    /**
     * Infomración que será pasada a la vista.
     *
     * @var \Emotion\Views\ViewBag
     */
    public $ViewBag = null;
    private $currentControllerAction = "Index";

    public function __construct() {
        // Leer del repositorio el usuario actual.
        $this->appUser = $repo = Core::getCredentialRepository()->readUser();
        $authenticated = $this->appUser->isLogged();

        $this->ViewBag = new \Emotion\Views\ViewBag(array(
            "app" => Core::info(), 
            "authenticated" => $authenticated, 
            "currentUser" => $this->appUser,
        ));
    }

    public function assign($key, $value) {
        $this->ViewBag[$key] = $value;
    }

    public function run($actionName) {
        Core::log("Este es el controlador que ejecutará {$actionName}");
        $this->currentControllerAction = $actionName;

        // Recuperar la lista de parámetros
        $method = new \ReflectionMethod(\get_class($this), $actionName);
        $params = $method->getParameters();
        $paramValues = array();
        $paramsOptions = array();

        foreach ($params as $param) {
            $paramValues[$param->name] = $param->isOptional() ? $param->getDefaultValue() : null;
            $paramsOptions[$param->name] = $param->isOptional();
        }

        foreach ($paramValues as $key => $value) {
            $found = false;

            if (isset($_GET[$key])) {
                $paramValues[$key] = $_GET[$key];
                $found = true;
            }

            // las variables de POST tienen prioridad sobre GET.
            if (isset($_POST[$key])) {
                $paramValues[$key] = $_POST[$key];
                $found = true;
            }

            if (!$found && $paramsOptions[$key] === false) {
                // Si se requiere y no fue encontrado lanzar error.
                throw new \Emotion\InternalException("Se requiere el argumento {$key} para poder continuar.");
            }
        }

        $output = null;

        if (count($paramValues) === 0) {
            Core::log("La solicitud no contiene parámetros. Se va a ejecutar {$actionName}");
            $output = $this->$actionName();
        } else {
            Core::log("La solicitud se ejecutará con " . count($paramValues) . " parámetros.");
            $output = call_user_func_array(array($this, $actionName), $paramValues);
        }
    
        Core::log("Ha finalizado la ejecución del controlador.");
        if ($output === null) {
            Core::log("El resultado es nulo, verifique que contiene la instrucción 'return \$this->View()'.");
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
        Core::log("Redirect:{$url}");
        return new \Emotion\Responses\RedirectResponse($url);
    }

    public function RedirectToAction($controllerAction = "Index", $controllerName = "", $params = "") {
        Core::log("RedirectToAction:{$controllerName}.{$controllerAction}");
        $url = \Emotion\Views\ViewHelpers::url("default", $controllerAction, $controllerName, $params);
        return $this->Redirect($url);
    }

    public function RedirectToRoute($routeName, $controllerAction = "Index", $controllerName = "", $params = "") {
        Core::log("RedirectToRoute:{$routeName}{$controllerName}.{$controllerAction}");
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