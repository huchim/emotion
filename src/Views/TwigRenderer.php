<?php namespace Emotion\Views;

use \Emotion\Contracts\IReadOnlyAppState;

class TwigRenderer implements IViewEngine {
    /**
     * Controlador a ejecutar.
     *
     * @var \Emotion\Controller
     */
    private $controller = null;

    private $controllerPaths = [];
    private $controllerBaseFolder = "";
    /**
     * Funciones varias.
     *
     * @var \Emotion\Views\ViewHelpers
     */
    private $helper = null;

    /**
     * Undocumented variable
     *
     * @var \Emotion\Contracts\IReadOnlyAppState
     */
    private $appState = null;

    public function __construct(IReadOnlyAppState $appState) {
        $this->appState = $configuration;
        $this->helper = new \Emotion\Views\ViewHelpers($appState);
    }

    public function setController(\Emotion\Controller $controller) {
        $this->controller = $controller;
    }

    public function loadTemplate($template) {

    }

    public function loadTemplateFromFile($file) {

    }

    public function setOptions($viewVars) {

    }

    public function getRender(\Emotion\Responses\ViewResponse $output, $viewBag) {
        // Obtener las rutas de la carpeta compartida y de la vista.
        $folders = $this->getSharedViewFolder();

        // Validar el acceso al archivo de la vista.
        $viewFileName = $this->getViewFileName();

        // Obtener el nombre de la vista a renderizar.
        $viewName = basename($viewFileName, ".html");

        // Agregar la carpeta del archivo actual.
        $folders[] = dirname($viewFileName);

        // Inicializar el renderizador de la vista.
        $loader = new \Twig_Loader_Filesystem($folders);
        $_twig = new \Twig_Environment($loader);

        // Plugins
        $this->helper->registerFilters($_twig);

        return $_twig->render("{$viewName}.html", $viewBag);
    }

    private function getViewFileName() {
        $paths = $this->controllerPaths;

        foreach ($paths as $viewPath) {
            if (file_exists($viewPath)) {
                return $viewPath;
            }
        }

        throw new \Exception("No se pudo localizar la vista, se buscó en " . $this->_exception_var($paths));
    }

    private function _exception_var($var) {
        $m = "";
        foreach ($var as $c) {
            $m .= "* " . $c . ",\n";
        }
        return $m;
    }

    private function getSharedViewFolder() {
        $controllerBaseFolder = $this->controllerBaseFolder;

        $controllerPaths = array(
            "{$controllerBaseFolder}/",
            "{$controllerBaseFolder}/shared/",
        );

        return $controllerPaths;
    }

    public function setFilePaths($viewPaths) {
        $this->controllerPaths = $viewPaths;
    }

    public function setBaseDir($baseDir) {
        $this->controllerBaseFolder = $baseDir;
    }
}