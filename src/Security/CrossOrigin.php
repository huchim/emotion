<?php namespace Emotion;

class CrossOrigin {
    /**
     * Agrega una IP o todas (*) a la lista de servidores que pueden
     * acceder a este servicio.
     *
     * @param string $origin IP o hostname que puede acceder.
     * @return void
     */
    public function enableOrigin($origin = "*") {
        header("Access-Control-Allow-Origin: {$origin}");
    }
}