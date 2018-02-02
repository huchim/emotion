<?php namespace Emotion\Configuration\Json;

use \Emotion\Contracts\Configuration\IConfigurationProvider;
use \Emotion\Contracts\Configuration\IConfigurationBuilder;
use \Emotion\Configuration\File\FileConfigurationSource;

class JsonConfigurationSource extends FileConfigurationSource {
    /**
     * Undocumented function
     *
     * @param \Emotion\Contracts\Configuration\IConfigurationBuilder $builder
     * @return \Emotion\Contracts\Configuration\IConfigurationProvider
     */
    public function build(IConfigurationBuilder $builder) {
        // El acceso a $builder, es para ver si algo ya ha sido cargado.
        return new JsonConfigurationProvider($this);
    }
}