<?php namespace Emotion;

use Emotion\Exceptions\ErrorException;
use Emotion\Exceptions\WarningException;

abstract class Utils {
    public static function registerErrorHandler() {
        set_error_handler(function ($err_severity, $err_msg, $err_file, $err_line, array $err_context)
        {
            // error was suppressed with the @-operator
            if (0 === error_reporting()) { return false;}
            switch($err_severity)
            {
                case E_ERROR:               throw new ErrorException            ($err_msg, 0, $err_severity, $err_file, $err_line);
                case E_WARNING:             throw new WarningException          ($err_msg, 0, $err_severity, $err_file, $err_line);
                default: return false;
            }
        });
    }
}