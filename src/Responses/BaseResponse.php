<?php namespace Emotion\Responses;

use \Emotion\Exceptions\ExceptionCodes;

abstract class BaseResponse {
    public $code = 200;
    public $message = "";
    public $hasContent = true;
    public $content = "";
    protected $innerException = null;

    public function process() {
        echo $this->content;
    }

    final public function tryProcess() {
        try {
            $this->process();
        } catch (\Exception $ex) {
            $innerException = $ex;

            if ($this->innerException !== null) {
                if ($this->innerException instanceof \Emotion\Exceptions\NotFoundException) {
                    // Si el error es porque no fue encontrado, entonces devuelvo ese error y
                    // no atrapo el error intermedio.
                    $innerException = $this->innerException ;
                } else {
                    // Existe un error previo que para que no se pierda
                    // lo envuelvo en un nuevo error.
                    $innerException = new \Emotion\Exceptions\InternalException(
                        $ex->getMessage(),
                        $ex->getCode(),
                        $this->innerException
                    );
                }
            }

            throw new \Emotion\Exceptions\InternalException(
                ExceptionCodes::S_RESPONSE_ERROR,
                ExceptionCodes::E_RESPONSE_ERROR,
                $innerException
            );
        }
    }
}