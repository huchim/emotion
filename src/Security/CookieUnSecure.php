<?php namespace Emotion\Security;

// use Emotion\Core;
use Emotion\HttpContext;
use Emotion\Exceptions\ExceptionCodes;
use Emotion\Exceptions\CredentialException;
use Emotion\Exceptions\RequiredClaimException;
use \Emotion\Contracts\Security\ICredentialRepository;

class CookieUnSecure implements ICredentialRepository {
    /**
     * Registro de eventos.
     *
     * @var \Emotion\Contracts\ILogger
     */
    private $logger = null;
    private $credentials = [];
    private $cookieName = "user";
    private $defaultPassword = "-huchim-";

    // $setuid:$setusername:$setpass:$usk:$pass:$setusertype:$setdisplayname:$setgroupname
    protected $customClaims = array("sub", "preferred_username", "password", "validation", "role", "name", "group");

    public function __construct() {
        $this->logger = new \Emotion\Loggers\Logger(self::class);
    }

    public function clearUser() {
        $this->clearCookie();
    }

    /**
     * @inheritDoc
     */
    public function readUser() {
        $this->logger->debug(0, "-----");
        $this->logger->debug(0, "Leyendo información del usuario en la memoria.");
        $this->readCookie();

        $this->logger->debug(0, "Construyendo lista de atributos...");
        $this->logger->debug(0, "Existen ". count($this->credentials) . " opciones en las credenciales actuales.");

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

        $this->logger->debug(0, "Existen ". count($standardOptions) . " opciones estándar y " . count($customOptions) . " personalizadas.");
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
        $this->logger->debug(0, "-----");
        $this->logger->debug(0, "Escribiendo información del usuario en la memoria.");

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
        $this->logger->debug(0, "Eliminando la cookie, el navegador no la tomará en cuenta en la siguiente solicitud.");
        $this->credentials = array();

        HttpContext::unsetCookie($this->cookieName);
    }

    private function isValidCookie($cookie) {
        $this->logger->debug(0, "Validando cookie");
        if ($cookie[0] === "") {
            $this->logger->debug(0, "La cookie no contiene el identificador en el primer índice.");
            return false;
        }

        $token = $this->getToken();
        $cookieValidationKey = $cookie[3];
        $cookieValidationCompareKey = md5($cookie[1] . $cookie[4] . $token . $cookie[5]);

        $this->logger->debug(0, "Comparando {$cookieValidationKey} con {$cookieValidationCompareKey}");
        return $cookieValidationKey === $cookieValidationCompareKey;
    }

    private function readCookieContent($cookieContent) {
        $this->logger->debug(0, "Decodificando el contenido de la cookie.");
        $cookieContent = base64_decode($cookieContent);
        $cookieContent = addslashes($cookieContent);
        $cookieContent = htmlentities($cookieContent, ENT_QUOTES);

        return explode(":", $cookieContent);
    }

    private function readCookie() {
        $this->logger->debug(0, "Leyendo información desde una cookie");
        $cookie = HttpContext::cookie($this->cookieName);

        if ($cookie === null) {
            $this->logger->info(0, "No existe la cookie de usuario en el sistema.");
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