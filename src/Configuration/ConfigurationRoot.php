<?php namespace Emotion\Configuration;

use \Emotion\Contracts\Configuration\IConfigurationSource;
use \Emotion\Contracts\Configuration\IConfigurationRoot;

class ConfigurationRoot implements IConfigurationRoot {
    /**
     * Undocumented variable
     *
     * @var \Emotion\Contracts\Configuration\IConfigurationProvider[]
     */
    private $providers = [];
    private $config = [];

    public function __construct(array $providers) {
        if ($providers != null) {
            $this->providers = $providers;
        }

        foreach ($this->providers as $provider) {
            $provider->load();
            $config = $provider->getData();

            if (!is_array($config)) {
                throw new \Exception("El proveedor no devolvió un arreglo correcto.");
            }

            $this->config = array_merge($this->config, $config);
        }
    }

    public function getValue($key) {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        return null;
    }

    public function getConnectionString($connectionName) {
        $connectionKey = "connectionStrings.{$connectionName}";
        return $this->getValue($connectionKey);
    }

    public function asArray() {
        return $this->config;
    }
}