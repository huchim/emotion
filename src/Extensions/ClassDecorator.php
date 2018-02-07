<?php namespace Emotion\Extensions;

abstract class ClassDecorator {
    protected $_instance;
    
    protected function setBaseClass($instance) {
        $this->_instance = $instance;
    }
    
    public function __call($method, $args) {
        return call_user_func_array(array($this->_instance, $method), $args);
    }

    public function __get($key) {
        return $this->_instance->$key;
    }

    public function __set($key, $val) {
        return $this->_instance->$key = $val;
    }
}
