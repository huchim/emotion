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

    public static function combinePaths($path1, $path2 = "") {
        // Eliminar diagonal final.
        if(substr($path1, -1) === '/') {
            $path1 = substr($path1, 0, -1);
        }

        if(substr($path2, 0, 1) === '/') {
            $path2 = substr($path2, 1);
        }

        if ($path2 !== "" && $path1 != "") {
            $path2 = "/{$path2}";
        }

        return $path1 . $path2;
    }
}