<?php namespace Emotion;

class CodeInjector {
    public function add($fileName, $once = false) {
        if ($once)
        {
            include_once $fileName;
        } else {
            include $fileName;
        }
    }

    /**
     * Intenta agregar un archivo PHP durante la ejecución.
     *
     * @param [type] $fileName Ruta de acceso al archivo.
     * @param boolean $once Utilizar include_once
     * @param boolean $throwError Intercepta el error o lo muestra.
     * @return void
     */
    public function tryToAdd($fileName, $once = false, $throwError = false) {
        if ($fileName === "") {
            if ($throwError) {
                throw new \Exception("No se puede agregar el archivo si no se proporciona la ruta de acceso.");
            }

            return;
        }


        try {
            $this->add($fileName, $once);
        } catch (Exception $ex) {
            if ($throwError) {
                throw $ex;
            }
        }
    }

    public $output = "";
}