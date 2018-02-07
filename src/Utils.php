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
    
    /**
     * Recupera la instancia de la ruta.
     * @param \Emotion\Contracts\IStaticFolderRoute $route
     * @return \Emotion\Contracts\IStaticFolderRoute
     */
    public static function getAsStaticFolderRoute(\Emotion\Contracts\IStaticFolderRoute $route) {
        return $route;
    }
    
    public static function getMimeType($filePath, $defaultMime = "text/plain") {
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimesSupported = array(
            "css" => " 	text/css",
            "png" => "image/png",
            "gif" => "image/gif",
            "jpg" => "image/jpeg",
            "js" => "application/x-javascript",
            "txt" => "text/plain",
            "html" => "text/html",
        );

        $selectedMime = $defaultMime;

        if (isset($mimesSupported[$fileExtension])) {
            $selectedMime = $mimesSupported[$fileExtension];
        }
        
        return $selectedMime;
    }
    
    public static function normalizePath($path) {
        return self::combinePaths($path) . "/";
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