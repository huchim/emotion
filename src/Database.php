<?php namespace Emotion;

use Aura\Sql\ExtendedPdo;
use \Emotion\Core;

class Database {
    protected static $instance = null;
    private $connections = [];
    private $defaultDriver = "sqlsrv";

    /**
     * Devuelve la conexión a la base de datos.
     *
     * @param string $connectionName
     * @return \Aura\Sql\ExtendedPdo
     */
    public function getConnection($connectionName = "default") {
        if (isset($this->connections[$connectionName])) {
            return $this->connections[$connectionName];
        }

        // Crear la instancia si no existe.
        $connectionOptions = Core::connectionStrings($connectionName);
        $server = $connectionOptions["server"];
        $database = $connectionOptions["database"];
        $driver = $this->defaultDriver;

        $this->connections[$connectionName] = new ExtendedPdo(
            "{$driver}:server={$server};database={$database}",
            $connectionOptions["uid"],
            $connectionOptions["password"]
        );

        return $this->connections[$connectionName];;
    }

    public static function connect($connectionName) {
        $self = Database::getInstance();
        return $self->getConnection($connectionName);
    }

    public static function query($connectionName, $query, $params = array()) {
        $connection = Database::connect($connectionName);
        $sth = $connection->prepare($query);
        $sth->execute($params);

        return $sth->fetchAll(\PDO::FETCH_ASSOC);;
    }

    /**
     * Devuelve una instancia única de la configuración.
     *
     * @return Database
     */
    public static function getInstance() {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

}