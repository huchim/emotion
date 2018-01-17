<?php namespace Emotion;

class AppUser extends \League\OpenIdConnectClaims\ClaimsSet
{
    protected $customAttributes = [];
    protected $customClaims = array("role", "group");

    public function __construct($standardAttributes = array(), $customAttributes = array()) {
        $this->attributes = $standardAttributes;
        $this->customAttributes = $customAttributes;
    }

    public function isLogged() {
        return count($this->attributes) !== 0 || count($this->customAttributes) !== 0;
    }

    public function getIdentifier() {
        try {
            return $this->getClaim("sub");
        } catch (\Exception $ex) {
            throw new \Emotion\InternalException("El identificador no esta configurado aún, utilice isLogged() para determinar si existe un usuario activo.", 0, $ex);
        }
    }

    public function getClaim($claim) {
        if (\array_key_exists($claim, $this->customAttributes)) {
            return $this->getCustomClaim($claim);
        }

        return parent::getClaim($claim);
    }

    private function setCustomClaim($attributeKey, $attributeValue = null) {
        if ($attributeValue === null) {
            $attributeValue = $this->getRawClaim($attributeKey);
        }

        $this->customAttributes[$attributeKey] = (string)$attributeValue;
    }

    private function getCustomClaim($attributeKey) {
        if (\array_key_exists($attributeKey, $this->customAttributes)) {
            return $this->customAttributes[$attributeKey];
        }        

        return "";
    }
}