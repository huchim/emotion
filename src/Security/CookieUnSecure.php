<?php namespace Emotion\Security;

use Emotion\Core;
use Emotion\HttpContext;
use Emotion\Exceptions\ExceptionCodes;
use Emotion\Exceptions\CredentialException;
use Emotion\Exceptions\RequiredClaimException;

class CookieUnSecure implements ICredentialRepository {
    private $credentials = [];
    private $cookieName = "user";
    private $defaultPassword = "-huchim-";

    // $setuid:$setusername:$setpass:$usk:$pass:$setusertype:$setdisplayname:$setgroupname
    protected $customClaims = array("sub", "preferred_username", "password", "validation", "role", "name", "group");

    public function clearUser() {
        $this->clearCookie();
    }

    /**
     * @inheritDoc
     */
    public function readUser() {
        Core::log("-----");
        Core::log("Leyendo información del usuario en la memoria.");
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
        );

        $customOptions = array(
            "role" => $this->getRawClaim("role"),
            "group" => $this->getRawClaim("group"),
            "password" => $this->defaultPassword,
        );

        Core::log("Existen ". count($standardOptions) . " opciones estándar y " . count($customOptions) . " personalizadas.");
        return new \Emotion\AppUser($standardOptions, $customOptions);
    }

    public function writeUser(\Emotion\AppUser $user) {
        try {
            return $this->internalWriteUser($user);
        } catch (CredentialException $ex) {
            throw $ex;
        } catch (\LogicException $ex) {
            throw new RequiredClaimException(ExceptionCodes::S_CLAIM_REQUIRED, ExceptionCodes::E_CLAIM_REQUIRED, $ex);
        } catch (\Exception $ex) {
            throw new CredentialException(ExceptionCodes::S_CLAIM_WRITE_ERROR, ExceptionCodes::E_CLAIM_WRITE_ERROR, $ex);
        }

        return -1;
    }

    private function internalWriteUser(\Emotion\AppUser $user) {
        Core::log("-----");
        Core::log("Escribiendo información del usuario en la memoria.");

        if ($user->getClaim("password") !== $this->defaultPassword) {
            throw new CredentialException(ExceptionCodes::S_CLAIM_PASSWORD, ExceptionCodes::E_CLAIM_PASSWORD);
        }

        $validationKey = $this->getValidationKey(
            $user->getClaim("preferred_username"),
            $user->getClaim("role"),
            $user->getClaim("name")
        );

        $options = [
            $user->getClaim("sub"),
            $user->getClaim("preferred_username"),
            $this->defaultPassword,
            $validationKey,
            $user->getClaim("role"),
            $user->getClaim("name"),
            $user->getClaim("group"),
        ];

        $output = base64_encode(implode(":", $options));
        
        // Requiere que se refresque la pagina.
        HttpContext::setCookie($this->cookieName, $output);

        return 0;
    }

    public function getToken() {
        return "]Mh+CuyQ.zcMcXU-;EsU_c#=`L_L9QnP";
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

        HttpContext::unsetCookie($this->cookieName);
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
        $cookie = HttpContext::cookie($this->cookieName);

        if ($cookie === null) {
            Core::log("No existe la cookie de usuario en el sistema.");
            return;
        }

        $credentials = $this->readCookieContent($cookie);
        $serial = $this->getToken();

        if ($this->isValidCookie($credentials)) {
            $this->credentials = $credentials;
        } else {
            $this->clearCookie();
        }
    }
}