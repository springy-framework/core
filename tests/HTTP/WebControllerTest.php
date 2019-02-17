<?php
/**
 * Test case for Springy\HTTP\WebController class.
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
use Springy\HTTP\WebController;
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
        Session::getInstance()->configure(
            Kernel::getInstance()->configuration()
        );

        // Starts authentication driver
        $app = app();
        $app->bind('user.auth.identity', function () {
            return new TstUser();
        });
        $app->bind('user.auth.driver', function ($c) {
            $user = $c['user.auth.identity'];

            return new AuthDriver($user);
        });
        $app->instance('user.auth.manager', function ($c) {
            return new Authentication($c['user.auth.driver']);
        });

        // Login the user
        $driver = app('user.auth.driver');
        $user = $driver->getIdentityById('test');
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
}

class TstController extends WebController
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