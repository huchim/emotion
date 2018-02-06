<?php namespace Emotion\Contracts;

interface IReadOnlyAppState {
    /**
     * Recupera los resultados de la operación "map" del enrutador.
     *
     * @return \AltoRouter
     */
    public function getRouterResults();

    /**
     * Devuelve la instancia del enrutador.
     *
     * @return \AltoRouter
     */
    public function getRouter();

    /**
     * Obtiene la URL base del enrutador.
     *
     * @return string
     */
    public function getRouterBase();

    /**
     * Devuelve la cadena de conexión existente en la configuración.
     *
     * @param string $connectionName Nombre de la conexión.
     * @return array
     */
    public function connectionStrings($connectionName);

    /**
     * Obtiene la configuración de la aplicación.
     *
     * @return \Emotion\Contracts\Configuration\IConfigurationRoot
     */
    public function getConfiguration();
}