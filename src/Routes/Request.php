<?php namespace Emotion\Routes;

class Request {
    private $post = array();
    private $get = array();
    private $name = "";
    private $action = "";

    public function __construct($name, $action, $get, $post) {
        $this->name = $name;
        $this->action = $action;
        $this->get = isset($_GET) ? $_GET : array();
        $this->post = isset($_POST) ? $_POST : array();
    }

    public function getMethod() {
        return "GET";
    }

    public function getControllerName() {
        return $this->name;
    }

    public function getControllerAction() {
        return $this->action;
    }
}