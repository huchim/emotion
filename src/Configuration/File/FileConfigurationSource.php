<?php namespace Emotion\Configuration\File;

use \Emotion\Contracts\Configuration\IConfigurationBuilder;
use \Emotion\Contracts\Configuration\IConfigurationSource;
use \Emotion\Contracts\Configuration\IConfigurationProvider;

class FileConfigurationSource implements IConfigurationSource {
    /**
     * Undocumented variable
     *
     * @var \Emotion\Contracts\Configuration\IFileProvider
     */
    public $fileProvider = null;

    /**
     * Undocumented function
     *
     * @param \Emotion\Contracts\Configuration\IConfigurationBuilder $builder
     * @return \Emotion\Contracts\Configuration\IConfigurationProvider
     */
    public function build(IConfigurationBuilder $builder) {
        // El acceso a $builder, es para ver si algo ya ha sido cargado.
        return new FileConfigurationProvider($this);
    }
}