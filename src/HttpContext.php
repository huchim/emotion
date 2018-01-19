<?php namespace Emotion;

class HttpContext {
    private static $instance = null;
    private $__server = [];
    private $__post = [];
    private $__get = [];
    private $__files = [];
    private $__request = [];
    private $__session = [];
    private $__env = [];
    private $__cookie = [];

    private function __construct() {
        $this->__server = !isset($_SERVER) ? [] : $_SERVER;
        $this->__post = !isset($_POST) ? [] : $_POST;
        $this->__get = !isset($_GET) ? [] : $_GET;
        $this->__files = !isset($_FILES) ? [] : $_FILES;
        $this->__request = !isset($_REQUEST) ? [] : $_REQUEST;
        $this->__session = !isset($_SESSION) ? [] : $_SESSION;
        $this->__env = !isset($_ENV) ? [] : $_ENV;
        $this->__cookie = !isset($_COOKIE) ? [] : $_COOKIE;
    }

    /**
     * Devuelve una instancia única de la configuración.
     *
     * @return HttpContext
     */
    public static function getInstance() {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    public function getVarValue($type, $name) {
        $var_type = "__{$type}";
        if (isset($this->$var_type[$name])) {
            return $this->$var_type[$name];
        }

        return null;
    }

    public function setVarValue($type, $name, $value) {
        $var_type = "__{$type}";
        if (isset($this->$var_type)) {
            $this->$var_type[$name] = $value;
            return;
        }

        throw new \Exception("La variable de contexto {$var_type} no esta definida.");
    }

    public function unsetVarValue($type, $name) {
        $var_type = "__{$type}";
        if (isset($this->$var_type)) {
            unset($this->$var_type[$name]);
            return;
        }
    }

    public function setVar($type, $value) {
        $var_type = "__{$type}";
        if (isset($this->$var_type)) {
            $this->$var_type = $value;    
            return;
        }

        throw new \Exception("La variable de contexto {$var_type} no esta definida.");
    }

    public static function __callStatic($name, $arguments) {
        $self = self::getInstance();
        $name = strtolower($name);
        $bulkLoad = strpos($name, "load") !== false;
        $vars = ["server", "post", "get", "files", "request", "session", "env", "cookie"];

        if (!in_array($name, $vars)) {
            $name = strtoupper($name);
            throw new \Exception("La variable de contexto $_{$name} no existe.", 2);
        }

        if (count($arguments) > 2 && count($arguments) < 1) {
            throw new \Exception("Argumentos insuficientes: " . count($arguments));
        }

        if (count($arguments) === 1) {
            if (!$bulkLoad) {
                // read only
                return $self->getVarValue($name, $arguments[0]);
            } else {
                // lista de key&value a cargar multiple...
                $v = $arguments[0];
                $name = str_replace("load", "", $name);
                $self->setVar($name, $v);
            }
            
        }

        if (count($arguments) === 2) {
            // setter
            $self->setVarValue($name, $arguments[0], $arguments[1]);
            return 0;
        }
    }

    public static function setCookie($name, $content, $expire = 3600, $path = "/") {
        $self = self::getInstance();

        @setcookie($name, $content, $expire, $path);

        // Refresh cookie vars
        if ($expire <= 0) {
            $self->unsetVarValue("cookie", $name);
        } else {
            $self->setVarValue("cookie", $name, $content);
        }
    }

    public static function unsetCookie($cookieName, $path = "/") {
        $self = self::getInstance();
        $self->setCookie($cookieName, null, -1, $path);
    }
}