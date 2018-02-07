<?php


namespace Emotion\Core;

use \Emotion\Contracts\IStaticFolderRoute;
use \Emotion\Contracts\IReadOnlyAppState;

/**
 * Description of StaticFolderRoute
 *
 * @author huchim
 */
class StaticFolderRoute implements IStaticFolderRoute {
    private $callback = null;
    private $defaultDocument = null;
    private $directory = null;
    private $rule = "{virtualFolder}[*:publicFile]";
    private $virtualDirectory = null;
    private $ruleName = null;
    private $requestFileName = null;
    
    /**
     *
     * @var \Emotion\Contracts\IReadOnlyAppState
     */
    private $appState = null;
    
    //put your code here
    public function getCallback() {
        return $this->callback;
    }

    public function getDefaultDocument() {
        return $this->defaultDocument;
    }

    public function getDirectory() {
        return $this->directory;
    }

    public function getRules() {
        $virtualFolder = "";
        $expectedRules = [$this->rule];
        
        if ($this->virtualDirectory === null) {
            $virtualFolder = $this->directory . "/";
        }
        
        if ($this->defaultDocument != null) {
            // Cuando existe un documento "predeterminado", se debe agregar dos
            // rutas.
            // 
            // 1.- Que responda ante /archivo.txt /[*:publicFile]
            // 2.- Que responsa a la raiz del directorio: /
            // 
            // De tal manera que la primera 
            //
            $expectedRules[] = "{virtualFolder}/";
        }
        
        // Agregar el directorio virtual si es necesario.
        $finalRules = [];
        foreach ($expectedRules as $rule) {
            $finalRules[] = str_replace("{virtualFolder}", $virtualFolder, $rule);
        }
        
        return $finalRules;
    }
    
    public function getRuleName() {
        return $this->ruleName;
    }

    public function getVirtualDirectory() {
        return $this->virtualDirectory;
    }

    public function setCallback($callback) {
        $this->callback = $callback;
    }

    public function setDefaultDocument($defaultDocument) {
        $this->defaultDocument = $defaultDocument;
    }

    public function setDirectory($directory) {
        $this->directory = $directory;
    }

    public function setRule($rule, $ruleName = "") {
        $this->rule = $rule;
        $this->ruleName = $ruleName;
    }

    public function setVirtualDirectory($virtualDirectory) {
        $this->virtualDirectory = $virtualDirectory;
    }

    public function getReadOnlyAppState(): \Emotion\Contracts\IReadOnlyAppState {
        return $this->appState;
    }

    public function setReadOnlyAppState(IReadOnlyAppState $appState) {
        $this->appState = $appState;
    }

    public function getRequestFileName() {
        return $this->requestFileName;
    }

    public function setRequestFileName($fileName) {
        $this->requestFileName = $fileName;
    }

}
