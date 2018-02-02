<?php namespace Emotion\Configuration\Memory;

use \Emotion\Contracts\Configuration\IConfigurationProvider;
use \Emotion\Contracts\Configuration\IConfigurationBuilder;
use \Emotion\Contracts\Configuration\IConfigurationSource;

class MemoryConfigurationSource implements IConfigurationSource {
    /**
     * Configuración inicial.\a
     *
     * @var array
     */
    public $config = [];

    /**
     * Undocumented function
     *
     * @param \Emotion\Contracts\Configuration\IConfigurationBuilder $builder
     * @return \Emotion\Contracts\Configuration\IConfigurationProvider
     */
    public function build(IConfigurationBuilder $builder) {
        // El acceso a $builder, es para ver si algo ya ha sido cargado.
        return new MemoryConfigurationProvider($this);
    }
}