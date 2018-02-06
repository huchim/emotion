<?php namespace Emotion\Contracts\Database;

interface IDatabase {
    /**
     * Devuelve la conexión a la base de datos.
     *
     * @param string $connectionName
     * @return \Aura\Sql\ExtendedPdo
     */
    public function getConnection($connectionName = "default");

    /**
     * Devuelve la conexión a la base de datos.
     *
     * @param string $connectionName
     * @return \Aura\Sql\ExtendedPdo
     */
    public function connect($connectionName);

    /**
     * Undocumented function
     *
     * @param string $connectionName
     * @param string $query
     * @param array $params
     * @return array
     */
    public function query($connectionName, $query, $params = array());
}