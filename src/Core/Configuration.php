<?php namespace Emotion\Core\Configuration;

class Configuration {
    /**
     * Configuración
     *
     * @var \Emotion\Configuration\ConfigurationCore
     */
    private $configuration = null;
    
    public function init() {
        // Unir la configuración de la aplicación.
        $this->info = array_merge(
            JsonConfig::tryGetJson("package.json"),
            JsonConfig::tryGetJson("app.json"));
            
        // Actualizar la configuración desde el arreglo.
        $this->configuration->loadConfigFromArray($this->info);

        if (isset($this->info["basePath"])) {
            self::setRouterBase($this->info["basePath"]);
        }
    }

    public static function getConfigurationObject() {
        $self = self::getInstance();
        return $self->configuration;
    }

    public static function loadConfig($fileName) {
        // Actualiza la configuración.
        $configuration = Core::getConfigurationObject();
        $configuration->loadConfig($fileName);
        $self->init();
    }

    public static function info() {
        return Core::getInstance()->info;
    }

    public static function get($option) {
        $self = Core::getInstance();

        if (isset($self->info[$option])) {
            return $self->info[$option];
        }

        return "";
    }

    /**
     * Devuelve una lista de propiedades de la conexión.
     *
     * @param string $connectionName Nombre de la conexión.
     * @return array
     */
    public static function connectionStrings($connectionName) {
        $connections = Core::get("connectionStrings");

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
}
