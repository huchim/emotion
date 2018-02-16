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

    public function execute($query, $params = array(), $connectionName = null) {
        if ($connectionName === null) {
            $connectionName = $this->connectionName;
        }

        $this->logger->trace(0, "Connection name: {$connectionName}");
        $connection = $this->getConnection($connectionName);

        $this->logger->trace(0, "SQL: {$query}");

        foreach ($params as $key => $value) {
            $this->logger->trace(0, "Param: {$key}={$value}");
        }

        $normalizedParams = [];

        foreach ($params as $k => $v) {
            // fetchAffected no soporta los parámetros que inicien con ":".
            $key = str_replace(":", "", $k);
            $normalizedParams[$key] = $v;
        }

        $this->logger->debug(0, "Ejecutando instrucciones...");
        return $connection->fetchAffected($query, $normalizedParams);
    }
}