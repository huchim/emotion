<?php namespace Emotion\Responses;

class HtmlResponse extends BaseResponse {
    public function __construct($content) {
        $this->content = $content;
    }
}