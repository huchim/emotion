<?php
use PHPUnit\Framework\TestCase;
use Emotion\Exceptions\ExceptionCodes;

class CookieUnSecureTest extends TestCase
{
    /**
     * Undocumented variable
     *
     * @var \Emotion\Security\ICredentialRepository
     */
    private $instance = null;

    public function setUp() {
        $this->instance = new \Emotion\Security\CookieUnSecure();
    }

    public function test_getToken()
    {
        $this->assertNotEmpty($this->instance->getToken());
    }

    /**
     * Solo se puede guardar la credencial con los atributos requeridos.
     *
     * @return void
     */
    public function testExceptionWriteUser() {
        $this->expectException(\Emotion\Exceptions\RequiredClaimException::class);

        // writeUser no debe poder escribir credenciales inválidas.
        // Una credencial inválida no contiene [sub,preferred_user,role]
        $actual = $this->instance->writeUser(new \Emotion\AppUser());
    }

    /**
     * Esta clase no permite modificar la contraseña.
     *
     * @return void
     */
    public function testWriteUserInvalidPassword() {
       $this->expectException(\Emotion\Exceptions\CredentialException::class);
       $this->expectExceptionCode(ExceptionCodes::E_CLAIM_PASSWORD);
       
        $actual = $this->getTesterUser();
        $actual->setPassword("test");

        $ok = $this->instance->writeUser($actual);
    }

    /**
     * Una credencial inválida o vacía produce un error al llamar 
     *
     * @return void
     */
    public function testGetIdentifierOnInvalidCookie() {
        $this->expectException(\Emotion\Exceptions\CredentialException::class);
       $this->expectExceptionCode(ExceptionCodes::E_CLAIM_EMPTY);

        \Emotion\HttpContext::setCookie("user", "notvalidcontent");
        $actual = $this->instance->readUser();
        $c = $actual->getIdentifier();

    }

    /**
     * Verifica que el usuario escrito sea igual al que luego se obtenga.
     *
     * @return void
     */
    public function testWriteUser() {
        $expected = $this->getTesterUser();
        $ok = $this->instance->writeUser($expected);

        $actual = $this->instance->readUser();       
        $this->assertEquals($expected, $actual);
    }

    /**
     * Undocumented function
     *
     * @return \Emotion\AppUser
     */
    private function getTesterUser() {
        $user = new \Emotion\AppUser();
        $user->setIdentifier("1");
        $user->setUsername("test");
        $user->setPassword("-huchim-");

        $user->setRole("tester");
        $user->setName("Tester User");
        $user->setGroup("Tester Group");

        return $user;
    }
}