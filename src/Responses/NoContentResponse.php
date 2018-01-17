<?php namespace Emotion\Responses;

class NoContentResponse extends BaseResponse {
    private $errorCode = 204;
    private $errorMessage = "No Content";

    public function __construct() {
    }

    public function process() {
        header( $_SERVER["SERVER_PROTOCOL"] . " {$this->errorCode} {$this->errorMessage}");
    }
}