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
use Springy\Core\Kernel;
use Springy\HTTP\Session;
use Springy\Security\AclUserInterface;
use Springy\Security\AuthDriverInterface;
use Springy\Security\Authentication;
use Springy\Security\IdentityInterface;

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
        $this->conf = [
            'SYSTEM_NAME'       => 'Foo',
            'SYSTEM_VERSION'    => [1, 0, 0],
            'PROJECT_CODE_NAME' => 'Alpha',
            'CHARSET'           => 'UTF-8',
            'TIMEZONE'          => 'UTC',
            'ENVIRONMENT'       => 'test',
            'CONFIG_PATH'       => __DIR__.'/../conf',
        ];

        $kernel = new Kernel($this->conf);

        Session::getInstance()->configure(
            $kernel->configuration()
        );

        $this->user = new User();
        $this->authDriver = new AuthDriver($this->user);
        $this->authentication = new Authentication($this->authDriver);
    }

    public function testAttempt()
    {
        $this->assertFalse($this->authentication->attempt('Foo', 'Bar'));
        $this->assertTrue($this->authentication->attempt('Homer', 'Duh!', false, false));
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
        $this->assertFalse($this->authentication->validate('Foo', 'Bar'));
        $this->assertTrue($this->authentication->validate('Homer', 'Duh!'));
    }
}

class AuthDriver implements AuthDriverInterface
{
    protected $identity;

    public function __construct(IdentityInterface $identity = null)
    {
        $this->setDefaultIdentity($identity);
    }

    public function getIdentitySessionKey(): string
    {
        return $this->identity->getSessionKey();
    }

    public function isValid(string $login, string $password): bool
    {
        return $login === 'Homer' && $password === 'Duh!';
    }

    public function setDefaultIdentity(IdentityInterface $identity)
    {
        $this->identity = $identity;
    }

    public function getDefaultIdentity(): IdentityInterface
    {
    }

    public function getLastValidIdentity(): IdentityInterface
    {
    }

    public function getIdentityById($iid): IdentityInterface
    {
        $this->identity->loadByCredentials([
            $this->identity->getIdField() => $iid,
        ]);

        return $this->identity;
    }
}

class User implements IdentityInterface, AclUserInterface
{
    public $uid;
    public $name;

    public function loadByCredentials(array $data)
    {
        $uid = $data[$this->getIdField()] ?? null;

        if ($uid == 'test') {
            $this->uid = $uid;
            $this->name = 'Homer';
        }
    }

    public function fillFromSession(array $data)
    {
    }

    public function getId()
    {
        return $this->uid;
    }

    public function getIdField(): string
    {
        return 'uuid';
    }

    public function getSessionKey(): string
    {
        return 'T35T';
    }

    public function getSessionData(): array
    {
        return [
            'uuid' => $this->uid,
            'name' => $this->name,
        ];
    }

    public function getCredentials(): array
    {
        return [];
    }

    public function getPermissionFor(string $aclObjectName): bool
    {
        return false;
    }
}