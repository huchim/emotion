<?php namespace Emotion;

interface IControllerBase {
    public function run($actionName);
    public function getOptions();
    public function assign($key, $value);
    public function authorize($roleName);
}