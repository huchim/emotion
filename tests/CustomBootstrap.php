<?php

use Emotion\HttpContext;
use Emotion\Utils;

// Interceptar los errores de la aplicación.
Utils::registerErrorHandler();

\Emotion\HttpContext::server([
    "REMOTE_ADDR" => "127.0.0.1",
    "REMOTE_PORT" => "42060",
    "SERVER_PROTOCOL" => "HTTP/1.1",
    "SERVER_ADDR" => "127.0.0.1",
    "SERVER_NAME" => "localhost",
    "SERVER_PORT" => "80",
    "HTTP_HOST" => "localhost",
    "HTTP_USER_AGENT" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0",
    "HTTP_ACCEPT" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
    "HTTP_CONNECTION'" => "keep-alive",
    
    "SCRIPT_FILENAME" => "tests/index.php",
    
    "REQUEST_METHOD" => "GET",
], null);