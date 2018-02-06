<?php 

use \Emotion\ControllerBase;

class HomeController extends ControllerBase {
    public function Index() {
        $config = $this->getConfiguration();

        // Crear instancia a base de datos.
        $db = new \Emotion\Database($config);

        return "Se ejecutó Index.";
    }
}