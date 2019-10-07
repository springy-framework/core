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
use Springy\Core\Configuration;
use Springy\HTTP\Controller;
use Springy\HTTP\Kernel;
use Springy\HTTP\Session;
use Springy\Security\AuthDriver;

require_once __DIR__.'/../mocks/mockUser.php';

/**
 * @runTestsInSeparateProcesses
 */
class WebControllerTest extends TestCase
{
    public $controller;

    public function setUp()
    {
        $config = Configuration::getInstance();
        $kernel = Kernel::getInstance();

        $config->set('application.authentication.hasher', 'Springy\Security\BasicHasher');
        $config->set('application.authentication.identity', 'TstUser');
        $config->set('application.authentication.driver', function ($c) {
            $hasher = $c['user.auth.hasher'];
            $user = $c['user.auth.identity'];

            return new AuthDriver($hasher, $user);
        });

        $kernel->setUp(__DIR__.'/../conf/main.php');

        Session::getInstance()->configure($config);

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
        $this->assertTrue($this->controller->hasPermission());
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
