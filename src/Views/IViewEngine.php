<?php namespace Emotion\Views;

interface IViewEngine {
    public function setController(\Emotion\Controller $controller);
    public function loadTemplate($template);
    public function loadTemplateFromFile($file);
    public function setOptions($viewVars);
    public function getRender(\Emotion\Responses\ViewResponse $output, $viewBag);
    public function setFilePaths($viewPaths);
    public function setBaseDir($baseDir);
}