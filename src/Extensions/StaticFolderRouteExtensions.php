<?php namespace Emotion\Extensions;

use \Emotion\Contracts\IStaticFolderRoute;

class StaticFolderRouteExtensions extends ClassDecorator {
    public function __construct(IStaticFolderRoute $instance) {
        $this->setBaseClass($instance);
    }
    
    public function getBaseDirectory() {
        $thisObj = $this->_instance;
        $rootDirectory = $thisObj->getReadOnlyAppState()->getDirectoryBase();
        $folderLocation = $rootDirectory . $thisObj->getDirectory();
        
        return $folderLocation;
    }

    /**
     * Undocumented function
     *
     * @return \Emotion\Contracts\IStaticFolderRoute
     */
    public function getInstance() {
        return $this->_instance;
    }
}
