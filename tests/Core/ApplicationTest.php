<?php
/**
 * Test case for Springy\Core\Application class.
 *
 * @copyright 2015 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Core\Application;

class ApplicationTest extends TestCase
{
    private $app;

    protected function setUp(): void
    {
        $this->app = new Application();
    }

    public function testThatApplicationCanRegisterEvents()
    {
        $this->app->on('event', function () {
            // Do nothing
        });

        $this->assertTrue($this->app['events']->hasHandlersFor('event'));
    }

    public function testThatApplicationCanUnRegisterEvents()
    {
        $this->app->on('event', function () {
            // Do nothing
        });

        $this->app->off('event');

        $this->assertFalse($this->app['events']->hasHandlersFor('event'));
    }

    public function testThatApplicationCanFireEvents()
    {
        $this->app->on('event', function () {
            return 10;
        });

        $this->assertEquals([10], $this->app->fire('event'));
    }
}
