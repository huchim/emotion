<?php namespace Emotion\Configuration;

use \Emotion\Contracts\Configuration\IConfigurationSource;
use \Emotion\Contracts\Configuration\IConfigurationBuilder;

class ConfigurationBuilder implements IConfigurationBuilder {
    /**
     * Undocumented variable
     *
     * @var array
     */
    private $sources = [];

    /**
     * Undocumented function
     *
     * @param \Emotion\Contracts\Configuration\IConfigurationSource $source
     * @return \Emotion\Contracts\Configuration\IConfigurationBuilder
     */
    public function add(IConfigurationSource $source) {
        if ($source == null) {
            throw new \Exception("Error al agregar el recurso.");
        }

        $this->sources[] = $source;

        return $this;
    }

    /**
     * Undocumented function
     *
     * @return \Emotion\Contracts\Configuration\IConfigurationSource[]
     */
    public function getSources() {
        return $this->sources;
    }

    /**
     * Undocumented function
     *
     * @return \Emotion\Contracts\Configuration\IConfigurationRoot
     */
    public function build()
    {
        $providers = [];

        foreach ($this->sources as $source)
        {
            $provider = $source->build($this);
            $providers[] = $provider;
        }

        return new ConfigurationRoot($providers);
    }
}