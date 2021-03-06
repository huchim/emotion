<?php namespace Emotion;

use \Emotion\Contracts\IReadOnlyAppState;

class ViewEngine {
    /**
     * Controlador.
     *
     * @var \Emotion\Controller
     */
    private $controller = null;

    /**
     * Motor de vistas.
     *
     * @var Views\IViewEngine
     */
    private $renderer = null;
    private $controllerName = "";
    private $controllerAction = "";
    private $controllerBaseFolder = "";    

    /**
     * Undocumented variable
     *
     * @var \Emotion\Contracts\IReadOnlyAppState
     */
    private $appState = null;

    /**
     * Undocumented variable
     *
     * @var \AltoRouter
     */
    private $router = null;
    private $routerResults = [];

    public function __construct($controllerName, $controllerAction, $controllerBaseFolder, IReadOnlyAppState $appState = null) {
        $this->controllerName = $controllerName;
        $this->controllerAction = $controllerAction;
        $this->controllerBaseFolder = $controllerBaseFolder;

        $this->renderer = new \Emotion\Views\TwigRenderer($appState);
        
        // $this->renderer->setController($this->controller);
        $this->renderer->setFilePaths($this->getViewPaths());
        $this->renderer->setBaseDir($controllerBaseFolder);
    }

    public function render($output, $viewBag) {
        // Imprimir resultado.
        echo $this->renderer->getRender($output, $viewBag);
    }

    private function getViewPaths() {
        $controllerBaseFolder = $this->controllerBaseFolder;
        $controllerName = $this->controllerName;
        $controllerNameTest = \strtolower($controllerName);
        $controllerAction =  $this->controllerAction;

        $controllerPaths = array(
            "{$controllerBaseFolder}/{$controllerNameTest}/{$controllerAction}.html",
            "{$controllerBaseFolder}/{$controllerName}/{$controllerAction}.html",
            "{$controllerBaseFolder}/{$controllerName}/_{$controllerAction}.html",
            "{$controllerBaseFolder}/{$controllerName}/{$controllerAction}/_{$controllerAction}.html",
            "{$controllerBaseFolder}/{$controllerName}/{$controllerAction}/{$controllerAction}.html",
            "{$controllerBaseFolder}/{$controllerName}_{$controllerAction}.html",
            "{$controllerBaseFolder}/shared/{$controllerName}/{$controllerAction}.html",
            "{$controllerBaseFolder}/shared/{$controllerName}/_{$controllerAction}.html",
            "{$controllerBaseFolder}/shared/{$controllerName}/{$controllerAction}/_{$controllerAction}.html",
            "{$controllerBaseFolder}/shared/{$controllerName}/{$controllerAction}/{$controllerAction}.html",
            "{$controllerBaseFolder}/shared/{$controllerName}_{$controllerAction}.html",
        );

        return $controllerPaths;
    }
}