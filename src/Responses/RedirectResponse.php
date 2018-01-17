<?php namespace Emotion\Responses;

class RedirectResponse extends BaseResponse {
    private $url = "";

    public function __construct($url) {
        $this->url = $url;
        $this->hasContent = false;
    }

    public function process() {
        Header ("Location: {$this->url}");
    }
}