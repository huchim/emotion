<?php namespace Emotion\Responses;

class ViewResponse extends BaseResponse {
    private $controllerName = "";
    private $controllerAction = "";

    public function __construct($controllerName, $controllerAction, $model) {
        $this->controllerAction = $controllerAction;
        $this->controllerName = $controllerName;
        $this->content = $model;
    }

    public function getViewName() {
        return $this->controllerAction;
    }

    public function getControllerName() {
        return $this->controllerName;
    }

    public function process() {
        // no duvuelve HTML...
    }
}