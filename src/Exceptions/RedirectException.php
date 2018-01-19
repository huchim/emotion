<?php namespace Emotion\Exceptions;

class RedirectException extends \Exception {
    private $url = "/";

    public function __construct($url) {
        $this->url = $url;
    }

    public function getUrl() {
        return $this->url;
    }
 }