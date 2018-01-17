<?php namespace Emotion\Security;

use \Emotion\Database;

class Account extends AccountBase {
    public function Login($username, $password) {
        $this->executeMiddlewares("claims");
        $this->executeMiddlewares("repository");

        // Configurar los parámetros para generar la consulta:
        $query = $this->getLoginSql();

        // Actualizar parámetros antes de la consulta y obtener los resultados.
        $password = $this->hashPassword($password);
        $result = $this->query($query, array(
            "username" => $username,
            "password" => $password,
        ));

        if (count($result) !== 1) {
            return false;
        }

        return $this->createAppUser($result[0]);
    }

    private function getLoginSql() {
        $dbUsername = isset($this->claimsDbColumns["preferred_username"]) ? $this->claimsDbColumns["preferred_username"] : "";
        $dbPassword = isset($this->claimsDbColumns["password"]) ? $this->claimsDbColumns["password"] : "";
        $tableName = isset($this->repository["table"]) ? $this->repository["table"] : "";
        return "SELECT * FROM {$tableName} WHERE {$dbUsername} = :username AND {$dbPassword} = :password";
    }
}