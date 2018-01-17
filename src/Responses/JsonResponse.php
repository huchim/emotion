<?php namespace Emotion\Responses;

class JsonResponse extends BaseResponse {
    private $url = "";

    public function __construct($content) {
        $this->content = $content;
    }

    public function process() {
        header('Content-Type: application/json');
        echo json_encode($this->content);
    }
}