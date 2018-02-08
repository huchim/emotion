<?php namespace Emotion\Responses;

use \Emotion\HttpContext;
use \Emotion\Utils;

class ErrorResponse extends BaseResponse {
    private $errorCode = 500;
    private $errorMessage = "Unauthorized";

    public function __construct($errorCode, $errorMessage, $exception = null) {
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
        $this->innerException = $exception;
    }

    public function process() {
        header(HttpContext::server("SERVER_PROTOCOL") . " {$this->errorCode} {$this->errorMessage}");

        $isDebug = Utils::isDebug();

        if ($isDebug) {
            echo "Ocurrió un problema en el sistema, el código es <strong>{$this->errorCode} - {$this->errorMessage}</strong>. <br /><br />";

            if ($this->innerException !== null) {
                echo "<h2>Error details:</h3><pre style=\"color:red;\">" . $this->innerException->getMessage() . "</pre>";
            }
        }
    }
}