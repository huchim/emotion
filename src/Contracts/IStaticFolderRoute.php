<?php
namespace Emotion\Contracts;

use \Emotion\Contracts\IReadOnlyAppState;

/**
 *
 * @author huchim
 */
interface IStaticFolderRoute {
    public function setDirectory($directory);
    public function getDirectory();
    public function setRule($rule, $ruleName = "");
    /**
     * @return array
     */
    public function getRules();
    public function getRuleName();
    public function setVirtualDirectory($virtualDirectory);
    public function getVirtualDirectory();
    public function setDefaultDocument($defaultDocument);
    public function getDefaultDocument();
    
    /**
     * Recibe un IStaticFolderRoute en forma de $this.
     * $c->setCallback(function () { $config = $this->getConfiguration(); echo "Hola mundo"; });
     * @param Closure $callback
     */
    public function setCallback($callback);
    public function getCallback();
    
    public function setReadOnlyAppState(IReadOnlyAppState $appState);
    
    /**
     * @return \Emotion\Contracts\IReadOnlyAppState El estado de la aplicación.
     */
    public function getReadOnlyAppState();
    
    public function setRequestFileName($fileName);
    public function getRequestFileName();
}
