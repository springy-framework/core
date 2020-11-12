<?php
/**
 * Test case for Springy\Security\Authentication class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Core\Configuration;
use Springy\HTTP\Session;
use Springy\Security\AuthDriver;
use Springy\Security\Authentication;
use Springy\Security\BasicHasher;

require_once __DIR__ . '/../mocks/mockUser.php';

/**
 * @runTestsInSeparateProcesses
 */
class AuthenticationTest extends TestCase
{
    public $authDriver;
    public $authentication;
    public $user;

    public function setUp()
    {
        Session::getInstance()->configure(
            Configuration::getInstance()
        );

        $hasher = new BasicHasher();
        $this->user = new User();
        $this->authDriver = new AuthDriver($hasher, $this->user);
        $this->authentication = new Authentication($this->authDriver);
    }

    public function testAttempt()
    {
        $this->assertFalse($this->authentication->attempt('homer@springfield.local', 'Ha ha!'));
        $this->assertTrue($this->authentication->attempt('homer@springfield.local', 'Duh!', false, false));
    }

    public function testCheck()
    {
        $this->assertFalse($this->authentication->check());
    }

    public function testGetDriver()
    {
        $this->assertInstanceOf(AuthDriver::class, $this->authentication->getDriver());
    }

    public function testLogin()
    {
        $this->assertNull($this->authentication->login($this->user));
    }

    public function testLogout()
    {
        $this->assertNull($this->authentication->logout());
    }

    public function testLoginWithId()
    {
        $this->assertNull($this->authentication->loginWithId('test'));
    }

    public function testSetDriver()
    {
        $this->assertNull($this->authentication->setDriver($this->authDriver));
    }

    public function testUser()
    {
        $this->assertNull($this->authentication->user());
    }

    public function testValidete()
    {
        $this->assertFalse($this->authentication->validate('homer@springfield.local', 'Ha ha!'));
        $this->assertTrue($this->authentication->validate('homer@springfield.local', 'Duh!'));
    }
}
