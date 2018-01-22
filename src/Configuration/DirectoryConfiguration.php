<?php namespace Emotion\Configuration;

class DirectoryConfiguration
{
    public $root = "";
    public $app = "app";
    public $api = "api";
    public $vendor = "vendor/";    
    public $helper = "helpers";
    public $lib = "lib";
    public $controllerName = "Home";
    public $controllerAction = "Index";

    public function fromArray($config) {
        if (isset($config["root"])) { $this->root = $config["root"]; }
        if (isset($config["app"])) { $this->app = $config["app"]; }
        if (isset($config["api"])) { $this->api = $config["api"]; }
        if (isset($config["vendor"])) { $this->vendor = $config["vendor"]; }
        if (isset($config["helper"])) { $this->helper = $config["helper"]; }
        if (isset($config["lib"])) { $this->root = $config["lib"]; }
        if (isset($config["controllerName"])) { $this->controllerName = $config["controllerName"]; }
        if (isset($config["controllerAction"])) { $this->controllerAction = $config["controllerAction"]; }
    }

    public function toArray() {
        return array(
            "root" => $this->root,
            "app" => $this->app,
            "api" => $this->api,
            "vendor" => $this->vendor,
            "helper" => $this->helper,
            "lib" => $this->lib,
            "controllerName" => $this->controllerName,
            "controllerAction" => $this->controllerAction,
        );
    }
}