<?php namespace Emotion\Security;

use \Emotion\Core;

class CookieUnSecure implements ICredentialRepository {
    private $credentials = [];
    // $setuid:$setusername:$setpass:$usk:$pass:$setusertype:$setdisplayname:$setgroupname
    protected $customClaims = array("sub", "preferred_username", "password", "validation", "role", "name", "group");

    public function clearUser() {
        $this->clearCookie();
    }

    /**
     * @inheritDoc
     */
    public function readUser() {
        $this->readCookie();

        Core::log("Construyendo lista de atributos...");
        Core::log("Existen ". count($this->credentials) . " opciones en las credenciales actuales.");

        if (count($this->credentials) === 0) {
            return new \Emotion\AppUser();
        }

        $standardOptions = array(
            "sub" => $this->getRawClaim("sub"),
            "preferred_username" => $this->getRawClaim("preferred_username"),
            "name" => $this->getRawClaim("name"),
            "password" => "-huchim-",
        );

        $customOptions = array(
            "role" => $this->getRawClaim("role"),
            "group" => $this->getRawClaim("group"),
        );

        Core::log("Existen ". count($standardOptions) . " opciones estándar y " . count($customOptions) . " personalizadas.");
        return new \Emotion\AppUser($standardOptions, $customOptions);
    }

    public function writeUser(\Emotion\AppUser $user) {
        $validationKey = $this->getValidationKey(
            $user->getClaim("preferred_username"),
            $user->getClaim("role"),
            $user->getClaim("name")
        );

        $options = [
            $user->getClaim("sub"),
            $user->getClaim("preferred_username"),
            "-huchim-",
            $validationKey,
            $user->getClaim("role"),
            $user->getClaim("name"),
            $user->getClaim("group"),
        ];

        $output = base64_encode(implode(":", $options));
        
        // Requiere que se refresque la pagina.
        setcookie("user", $output, 0, "/");
    }

    private function getRawClaim($claimName) {
        $claimIndex = \array_search($claimName, $this->customClaims);

        if ($claimIndex !== false) {
            if (isset($this->credentials[$claimIndex])) {
                return $this->credentials[$claimIndex];
            }
        }

        return "";
    }


    private function getValidationKey($username, $role, $name) {
        $token = $this->getToken();

        return md5("{$username}{$role}{$token}{$name}");
    }

    private function clearCookie() {
        Core::log("Eliminando la cookie, el navegador no la tomará en cuenta en la siguiente solicitud.");
        $this->credentials = array();

        unset($_COOKIE["user"]);
        setcookie("user", null, -3600, "/");
    }

    private function getToken() {
        return "]Mh+CuyQ.zcMcXU-;EsU_c#=`L_L9QnP";
    }

    private function isValidCookie($cookie) {
        Core::log("Validando cookie");
        if ($cookie[0] === "") {
            Core::log("La cookie no contiene el identificador en el primer índice.");
            return false;
        }


        $token = $this->getToken();
        $cookieValidationKey = $cookie[3];
        $cookieValidationCompareKey = md5($cookie[1] . $cookie[4] . $token . $cookie[5]);

        Core::log("Comparando {$cookieValidationKey} con {$cookieValidationCompareKey}");
        return $cookieValidationKey === $cookieValidationCompareKey;
    }

    private function readCookieContent($cookieContent) {
        Core::log("Decodificando el contenido de la cookie.");
        $cookieContent = base64_decode($cookieContent);
        $cookieContent = addslashes($cookieContent);
        $cookieContent = htmlentities($cookieContent, ENT_QUOTES);

        return explode(":", $cookieContent);
    }

    private function readCookie() {
        Core::log("Leyendo información desde una cookie");
        if (isset($_COOKIE["user"])) {
            $user = $_COOKIE["user"];
        } else {
            Core::log("No existe la cookie de usuario en el sistema.");
            return;
        }

        $credentials = $this->readCookieContent($user);
        $serial = $this->getToken();

        if ($this->isValidCookie($credentials)) {
            $this->credentials = $credentials;
        } else {
            $this->clearCookie();
        }
    }
}