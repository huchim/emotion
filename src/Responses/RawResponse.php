<?php namespace Emotion\Responses;

class RawResponse extends BaseResponse {
    public function __construct($content) {
        $this->content = $content;
    }

    public function process() {
        echo $this->content;
    }
}