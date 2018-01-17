<?php namespace Emotion\Security;

use \Emotion\Database;

abstract class AccountBase {
    protected $claimsDbColumns = [];
    protected $repository = array("connection" => "default", "table" => "users");
    private $middlewares = [];
    private $middlewaresExecuted = [];
    protected $claimsNames = [
        'sub',
        'name',
        'given_name',
        'middle_name',
        'family_name',
        'nickname',
        'preferred_username',
        'profile',
        'picture',
        'website',
        'email',
        'email_verified',
        'gender',
        'birthdate',
        'zoneinfo',
        'locale',
        'phone_number',
        'phone_number_verified',
        'address',
    ];

    protected $claimsCustom = ["role", "group"];

    public function __construct() {
        // Definir la lista de columnas en la base de datos.
        foreach($this->claimsNames as $claimName) {
            $this->claimsDbColumns[$claimName] = $claimName;
        }

        foreach($this->claimsCustom as $claimName) {
            $this->claimsDbColumns[$claimName] = $claimName;
        }
    }

    protected function hashPassword($password) {
        return md5($password);
    }

    /**
     * Genera un usuario en base a una arreglo.
     *
     * @param array $userInfo
     * @return \Emotion\AppUser
     */
    protected function createAppUser($userInfo) {
        $standardClaims = [];
        $customClaims = [];

        foreach ($this->claimsDbColumns as $claimName => $claimColumn) {
            // It is standard
            if (in_array($claimName, $this->claimsNames)) {
                if (isset($userInfo[$claimColumn])) {
                    $standardClaims[$claimName] = $userInfo[$claimColumn];
                }
            }

            // It is custom
            if (in_array($claimName, $this->claimsCustom)) {
                if (isset($userInfo[$claimColumn])) {
                    $customClaims[$claimName] = $userInfo[$claimColumn];
                }
            }
        }

        return new \Emotion\AppUser($standardClaims, $customClaims);
    }

    public function matchColumn($claimName, $columnName) {
        $this->claimsDbColumns[$claimName] = $columnName;
    }

    public function configureRepository($connectionName, $tableName) {
        $this->repository["connection"] = $connectionName;
        $this->repository["table"] = $tableName;
    }

    public function middleware($type, $callback = null) {
        if (is_array($type)) {
            $this->middlewares = $type;
            return;
        }

        $this->middlewares[] = array($type => $callback);
    }

    protected function query($query, $params = array()) {
        $connectionname = $this->repository["connection"];
        return Database::query($connectionname, $query, $params);
    }

    protected function executeMiddlewares($type) {
        if (in_array($type, $this->middlewaresExecuted)) {
            return;
        }

        $funcs = $this->filterMiddlewares($type);

        foreach ($funcs as $func) {
            if ($func !== null) {
                $func($this);
            }
        }

        $this->middlewaresExecuted[] = $type;
    }

    private function filterMiddlewares($type) {
        $middlewaresSelected = [];

        foreach ($this->middlewares as $key => $callback) {
            if ($key === $type) {
                $middlewaresSelected[] = $callback;
            }
        }

        return $middlewaresSelected;
    }
}