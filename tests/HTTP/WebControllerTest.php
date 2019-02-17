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

        $app = app();
        $app->bind('user.auth.identity', function () {
            return new User();
        });
        $app->bind('user.auth.driver', function ($c) {
            $user = $c['user.auth.identity'];

            return new AuthDriver($user);
        });
        $app->instance('user.auth.manager', function ($c) {
            return new Authentication($c['user.auth.driver']);
        });

        $this->controller = new WebController();
    }

    public function testIncomplete()
    {
        $this->markTestIncomplete(
            'This test has not been fully implemented yet.'
        );
    }
}

