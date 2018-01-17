<?php namespace Emotion\Security;

interface ICredentialRepository {
    /**
     * Devuelve el usuario activo en la sesión.
     *
     * @return \Emotion\AppUser 
     */
    public function readUser();
    public function writeUser(\Emotion\AppUser $user);
    public function clearUser();
}