<?php
/**
 * Test case for Springy\HTTP\Controller class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Exceptions\Http403Error;
use Springy\HTTP\Controller;
use Springy\HTTP\Kernel;
use Springy\HTTP\Session;
use Springy\Security\AuthDriver;
use Springy\Security\Authentication;

require_once __DIR__.'/../mocks/mockUser.php';

/**
 * @runTestsInSeparateProcesses
 */
class WebControllerTest extends TestCase
{
    public $controller;

    public function setUp()
    {
        $kernel = new Kernel();
        $config = $kernel->configuration();

        $config->set('application.authentication.hasher', 'Springy\Security\BasicHasher');
        $config->set('application.authentication.identity', 'TstUser');
        $config->set('application.authentication.driver', function ($c) {
            $hasher = $c['user.auth.hasher'];
            $user = $c['user.auth.identity'];

            return new AuthDriver($hasher, $user);
        });

        $kernel->setUp(__DIR__.'/../conf/main.php');

        Session::getInstance()->configure($kernel->configuration());

        $kernel->setAuthDriver();
        // Login the user
        $driver = app('user.auth.driver');
        $user = $driver->getIdentityById('0001');
        app('user.auth.manager')->login($user, false);

        $this->controller = new TstController([
            'test',
            'controller',
        ]);
    }

    public function testHasPermission()
    {
        $this->assertTrue($this->controller->_hasPermission());
    }

    public function testPageNotFound()
    {
        $this->expectException(Http403Error::class);
        $this->controller->_forbidden();
    }
}

class TstController extends Controller
{
    protected $authNeeded = true;
}

class TstUser extends User
{
    public function hasPermissionFor(string $aclObjectName): bool
    {
        return $aclObjectName === 'TstController|test|controller';
    }
}
