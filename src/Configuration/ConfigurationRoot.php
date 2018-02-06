<?php namespace Emotion\Configuration;

use \Emotion\Contracts\Configuration\IConfigurationSource;
use \Emotion\Contracts\Configuration\IConfigurationRoot;
use \Emotion\Exceptions\ExceptionCodes;

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

            // Simplificar arreglo,
            $simplifyConfig = false;

            $simplifiedArray = $simplifyConfig ? [] : $config;

            if ($simplifyConfig) {
                foreach ($config as $key => $value) {
                    if (!is_array($value)) {
                        $simplifiedArray[$key] = $value;
                    } else {
                        $root = $key . ".";

                        foreach ($value as $innerKey => $innerValue) {
                            $simplifiedArray[$root . $innerKey] = $innerValue;
                        }
                    }
                }
            }

            $this->config = array_merge($this->config, $simplifiedArray);
        }
    }

    public function getValue($key) {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        return null;
    }

    public function getConnectionString($connectionName) {
        $connections = $this->getValue("connectionStrings");

        if (!is_array($connections)) {
            throw new \Exception(ExceptionCodes::S_CONNECTIONS_EMPTY, ExceptionCodes::E_CONNECTIONS_EMPTY);
        }

        if (!isset($connections[$connectionName])) {
            throw new \Exception(ExceptionCodes::S_CONNECTIONS_MISSING, ExceptionCodes::E_CONNECTIONS_MISSING);
        }

        $connectionParts = explode(";", $connections[$connectionName]);
        $connectionOptions = [];

        foreach ($connectionParts as $connectionOption) {
            $options = explode("=", $connectionOption);

            if (count($options) !== 2) {
                // Esta sección en la cadena no representa un patrón clave-valor.
                continue;
            }

            $optionName = strtolower($options[0]);
            $optionValue = $options[1];
            $connectionOptions[$optionName] = $optionValue;
        }
        
        return $connectionOptions;
    }

    public function asArray() {
        return $this->config;
    }

    public function updateValue($key, $value) {
        if ($this->config == null) {
            throw new \Exception("No se ha inicializado la configuración.");
        }

        $this->config[$key] = $value;
    }
}