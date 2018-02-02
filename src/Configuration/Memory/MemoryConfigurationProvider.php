<?php namespace Emotion\Configuration\Memory;

use \Emotion\Contracts\Configuration\IConfigurationProvider;

class MemoryConfigurationProvider implements IConfigurationProvider {
    /**
     * Undocumented variable
     *
     * @var MemoryConfigurationSource
     */
    private $source = null;
    private $data = [];

    /**
     * Undocumented function
     *
     * @param MemoryConfigurationSource $source
     */
    public function __construct(MemoryConfigurationSource $source) {
        $this->source = $source;
    }

    public function load($reload = false) {
        $this->data = $this->source->config;
    }

    public function getData() 
    {
        return $this->data;
    }

    public function set($key, $value) {
        $this->data[$key] = $value;
    }
}