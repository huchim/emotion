<?php namespace Emotion\Contracts\Database;

interface IDatabase {
    /**
     * Devuelve la conexión a la base de datos.
     *
     * @param string $connectionName
     * @return \Aura\Sql\ExtendedPdo
     */
    public function getConnection($connectionName = null);

    /**
     * Devuelve la conexión a la base de datos.
     *
     * @param string $connectionName
     * @return \Aura\Sql\ExtendedPdo
     */
    public function connect($connectionName);
    public function query($query, $params = array(), $connectionName = null);
    public function execute($query, $params = array(), $connectionName = null);
}