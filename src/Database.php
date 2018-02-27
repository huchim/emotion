<?php namespace Emotion;

use Aura\Sql\ExtendedPdo;
use \Emotion\Core;
use \Emotion\Contracts\Database\IDatabase;
use \Emotion\Contracts\Configuration\IConfigurationRoot;

class Database implements IDatabase {
    protected $instance = null;

    /**
     * Undocumented variable
     *
     * @var \Emotion\Contracts\Configuration\IConfigurationRoot
     */
    private $configuration = null;

    /**
     * Undocumented variable
     *
     * @var \Emotion\Contracts\ILogger
     */
    private $logger = null;

    /**
     * Undocumented variable
     *
     * @var \Aura\Sql\ExtendedPdo[]
     */
    private $connections = [];
    private $defaultDriver = "sqlsrv";
    private $connectionName = "default";

    /**
     * Undocumented function
     *
     * @param \Emotion\Contracts\Configuration\IConfigurationRoot $configuration
     */
    public function __construct(IConfigurationRoot $configuration, $connectionName = null) {
        $this->logger = new \Emotion\Loggers\Logger(self::class);

        // De aquí obtendremos las cadenas de configuración.
        $this->configuration = $configuration;

        // Establecer el nombre de la conexión predeterminado para esta instancia.
        if ($connectionName !== null) {
            $this->connectionName = $connectionName;
        }

        if ($connectionName === null && defined("APP_CONNECTION")) {
            $this->logger->debug(0, "Utilizando cadena de conexión predeterminada: " + APP_CONNECTION);
            $this->connectionName = APP_CONNECTION;
        }
    }

    /**
     * Devuelve la conexión a la base de datos.
     *
     * @param string $connectionName
     * @return \Aura\Sql\ExtendedPdo
     */
    public function getConnection($connectionName = null) {
        if ($connectionName === null) {
            $connectionName = $this->connectionName;
        }

        if (isset($this->connections[$connectionName])) {
            return $this->connections[$connectionName];
        }

        // Crear la instancia si no existe.
        $connectionOptions = $this->configuration->getConnectionString($connectionName);

        $server = $connectionOptions["server"];
        $database = $connectionOptions["database"];
        $driver = $this->defaultDriver;

        $this->logger->debug(0, "Creando conexión de tipo: {$connectionName}");
        $this->connections[$connectionName] = new ExtendedPdo(
            "{$driver}:server={$server};database={$database}",
            $connectionOptions["uid"],
            $connectionOptions["password"]
        );

        return $this->connections[$connectionName];;
    }

    public function connect($connectionName) {
        return $this->getConnection($connectionName);
    }

    public function query($query, $params = array(), $connectionName = null) {
        if ($connectionName === null) {
            $connectionName = $this->connectionName;
        }

        $this->logger->trace(0, "Connection name: {$connectionName}");
        $connection = $this->getConnection($connectionName);

        $this->logger->trace(0, "SQL: {$query}");
        $sth = $connection->prepare($query);

        $this->logger->debug(0, "Ejecutando consulta");
        $sth->execute($params);

        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Realiza una operación de inserción de datos.ExtendedPdo
     *
     * @param string $query Operación SQL a ejecutar.ExtendedPdo
     * @param array $params Lista de parámetros a sustituir dentro de la operación.ExtendedPdo
     * @param string $connectionName Nombre de la conexión a utilizar.ExtendedPdo
     * @return mixed Devuelve el último identificador de la operación.
     */
    public function insert($query, $params = array(), $colId = null, $connectionName = null) {
        // Establezco el nombre de conmexión predeterminado a la base de datos.
        if ($connectionName === null) {
            $connectionName = $this->connectionName;
        }

        $this->logger->trace(0, "Connection name: {$connectionName}");
        $this->logger->trace(0, "SQL: {$query}");

        // Recupero la instancia de conexión a la base de datos.
        $connection = $this->getConnection($connectionName);

        // Normalizo los párametros antes de ejecutar la consulta
        // para que tengan el formato requerido por PDO.
        $normalizedParams = $this->normalizeParams($params);

        // Preparo la operación a ejecutar.
        $statement = $connection->prepare($query);

        // Ejecuto la operación con los parámetros pasados a esta función, lo
        // cual me devolverá un número de filas afectadas.
        $affectedRows = $statement->execute($normalizedParams);        

        // Con la instancia de la conexión, puedo intentar recuperar el identificador
        // de la operación, que de acuerdo a la documentación funciona en todas
        // las bases de datos, o al menos en SQL Server y MySql, en Postgress requiere
        // de un parámetro adicional que en su momento encontraré la manera de agregar.
        $lastInsertId = $connection->lastInsertId($colId);

        $this->logger->trace(0, "Last inserted id: \"{$lastInsertId}\"");
    }

    public function execute($query, $params = array(), $connectionName = null) {
        if ($connectionName === null) {
            $connectionName = $this->connectionName;
        }

        $this->logger->trace(0, "Connection name: {$connectionName}");
        $this->logger->trace(0, "SQL: {$query}");

        $connection = $this->getConnection($connectionName);
        $normalizedParams = $this->normalizeParams($params);

        $this->logger->debug(0, "Ejecutando instrucciones...");
        return $connection->fetchAffected($query, $normalizedParams);
    }

    private function normalizeParams(array $params) {
        $normalizedParams = [];

        foreach ($params as $paramName => $paramValue) {
            // fetchAffected no soporta los parámetros que inicien con ":".
            $this->logger->trace(0, "Param: {$paramName}={$paramValue}");
            $key = str_replace(":", "", $paramName);
            $normalizedParams[$key] = $paramValue;
        }

        return $normalizedParams;
    }
}