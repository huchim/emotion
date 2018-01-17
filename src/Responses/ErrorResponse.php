<?php namespace Emotion\Responses;

class ErrorResponse extends BaseResponse {
    private $errorCode = 500;
    private $errorMessage = "Unauthorized";
    private $innerException = null;

    public function __construct($errorCode, $errorMessage, $exception = null) {
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
        $this->innerException = $exception;
    }

    public function process() {
        header( $_SERVER["SERVER_PROTOCOL"] . " {$this->errorCode} {$this->errorMessage}");

        $config = \Emotion\Configuration\CoreConfiguration::getInstance();

        if ($config->isDebug()) {
            echo "Ocurrió un problema en el sistema, el código es <strong>{$this->errorCode} - {$this->errorMessage}</strong>. <br /><br />";

            if ($this->innerException !== null) {
                echo "<h2>Error details:</h3><pre style=\"color:red;\">" . $this->innerException->getMessage() . "</pre>";
            }
        }
    }
}