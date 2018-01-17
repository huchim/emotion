<?php namespace Emotion\Responses;

abstract class BaseResponse {
    public $code = 200;
    public $message = "";
    public $hasContent = true;
    public $content = "";

    public function process() {
        echo $this->content;
    }
}