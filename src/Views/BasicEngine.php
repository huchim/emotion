<?php namespace Emotion\Views;

class BasicEngine implements IViewEngine {
    /**
     * Controlador a ejecutar.
     *
     * @var \Emotion\Controller
     */
    private $controller = null;

    /**
     * Undocumented variable
     *
     * @var \Emotion\CodeInjector
     */
    private $codeInjector = null;

    public function __construct() {
        if (!function_exists("twig_get_template_file_name")) {
            function twig_get_template_file_name($fileName) {
                $config = \Emotion\Configuration\CoreConfiguration::getInstance()->getConfig();
                return str_replace("~/", "{$config->app}/", $fileName);
            }
        }

        if (!function_exists("twig_get_template_file_base")) {
            function twig_get_template_file_base($fileName) {
                return basename(twig_get_template_file_name($fileName), ".html");
            }
        }

        if (!function_exists("twig_get_template_dir")) {
            function twig_get_template_dir($fileName) {
                return dirname(twig_get_template_file_name($fileName)) . "/";
            }
        }

        if (!function_exists("twig_init_instance")) {
            function twig_init_instance($initial_path) {
                global $_twig;

                $fileName = twig_get_template_file_name($initial_path);                
                $templateDir = twig_get_template_dir($fileName);

                if (!isset($_twig)) {
                    $loader = new \Twig_Loader_Filesystem([$templateDir]);
                    $_twig = new \Twig_Environment($loader);
                }
            }
        }

        if (!function_exists("twig_update_paths")) {
            function twig_update_paths($fileName) {
                global $_twig;

                $fileName = twig_get_template_file_name($fileName);                
                $templateDir = twig_get_template_dir($fileName);                
                $baseName = twig_get_template_file_base($fileName);

                if (!isset($_twig)) {
                    // Inicializar si es requerido y terminar.
                    twig_init_instance($fileName);
                    return;
                }
                
                $loader = $_twig->getLoader();
                $paths = $loader->getPaths();
                
                if (!in_array($templateDir, $paths)) {
                    $paths[] = $templateDir;

                    $loader = new \Twig_Loader_Filesystem($paths);
                    $_twig = new \Twig_Environment($loader);
                }
            }
        }

        if (!function_exists("close")) {
            function close($fileName = "") {
                global $_twig, $_twig_active_template;

                if ($fileName === "") {
                    $fileName = $_twig_active_template;
                }
                
                $fileName = twig_get_template_file_name($fileName);
                $baseName = twig_get_template_file_base($fileName);

                twig_update_paths($fileName);
                
                echo $_twig->render("{$baseName}.f.html", array("demo", "hola"));
            }
        }

        if (!function_exists("layout")) {
            function layout($fileName = "", $options = array()) {
                global $_twig, $_twig_active_template;

                if ($fileName === "") {
                    // Cerrar el layout activo.
                    close();
                    return;
                }
                
                $fileName = twig_get_template_file_name($fileName);
                $baseName = twig_get_template_file_base($fileName);

                twig_update_paths($fileName);

                // Activar este template:
                $_twig_active_template = $fileName;
                
                echo $_twig->render("{$baseName}.html", $options);
            }
        }
    }

    public function setController(\Emotion\Controller $controller) {
        $this->controller = $controller;
        $this->codeInjector = new \Emotion\CodeInjector();
    }

    public function loadTemplate($template) {

    }

    public function loadTemplateFromFile($file) {

    }

    public function setOptions($viewVars) {

    }

    public function getRender($viewName) {
        return "";
    }

    public function preRender($viewName) {
        // Este renderizador únicamente incluye el archivo index.php
        $targetViewPart = $this->controller->getControllerPart($viewName);

        if ($targetViewPart === "" && $viewName !== "INDEX") {
            // No se ha encontrado la vista deseada, se intentará recuperar el INDEX.
            $targetViewPart = $this->controller->getControllerPart("INDEX");
        }

        $this->codeInjector->add($targetViewPart);
    }

    public function render($viewName = "index") {
        return "";
    }
}