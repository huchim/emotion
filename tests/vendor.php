<?php
// Definir la zona del script.
date_default_timezone_set("America/Merida");

$loader = require 'vendor/autoload.php';

// Soportar solamente API.
\Emotion\Core::addMvcApi();

// Correr la aplicación.
\Emotion\Core::run();