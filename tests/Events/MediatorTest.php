<?php
/**
 * Test case for Springy\Events\Mediator class.
 *
 * @copyright 2015 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Container\DIContainer;
use Springy\Events\Mediator;

class MediatorTest extends TestCase
{
    protected $mediator;
    protected $container;

    protected function setUp(): void
    {
        $this->container = new DIContainer();
        $this->mediator = new Mediator($this->container);
    }

    public function testThatMediatorCanRegisterAndForgetHandlers()
    {
        $event = 'global.someevent';
        // Normal
        $this->mediator->registerHandlerFor($event, function () {
            // Do nothing
        });
        $this->assertTrue($this->mediator->hasHandlersFor($event));

        $this->mediator->forget($event);
        $this->assertFalse($this->mediator->hasHandlersFor($event));

        // Alternative
        $this->mediator->on($event, function () {
            // Do nothing
        });
        $this->assertTrue($this->mediator->hasHandlersFor($event), 'message');

        $this->mediator->off($event);
        $this->assertFalse($this->mediator->hasHandlersFor($event));
    }

    public function testThatMediatorFiresASingleRegisteredEvent()
    {
        $toChange = 'not-changed';

        $this->mediator->on('global.aevent', function () use (&$toChange) {
            $toChange = 'has-changed';
        });

        $this->assertEquals('not-changed', $toChange);

        $this->mediator->fire('global.aevent');

        $this->assertEquals('has-changed', $toChange);
    }

    public function testThatMediatorCanFireAnEventAndNotifySeveralHandlers()
    {
        $dataToChange = [];

        for ($i = 0; $i < 5; $i++) {
            $dataToChange[$i] = 'not-changed' . $i;

            $this->mediator->on('global.event', function () use (&$dataToChange, $i) {
                $dataToChange[$i] = 'has-changed' . $i;
            });
        }

        for ($i = 0; $i < 5; $i++) {
            $this->assertEquals('not-changed' . $i, $dataToChange[$i]);
        }

        $this->mediator->fire('global.event');

        for ($i = 0; $i < 5; $i++) {
            $this->assertEquals('has-changed' . $i, $dataToChange[$i]);
        }
    }

    public function testThatMediatorRespectsAPriorityOrderWhenFiringEvents()
    {
        $toChangeFirst = 0;
        $toChangeSecond = 0;
        $toChangeThird = 0;

        $this->mediator->on('event', function () use (&$toChangeSecond) {
            usleep(100);
            $toChangeSecond = microtime(true);
        }, 2);

        $this->mediator->on('event', function () use (&$toChangeThird) {
            usleep(100);
            $toChangeThird = microtime(true);
        });

        $this->mediator->on('event', function () use (&$toChangeFirst) {
            usleep(100);
            $toChangeFirst = microtime(true);
        }, 10);

        $this->mediator->fire('event');

        $this->assertGreaterThan($toChangeFirst, $toChangeSecond);
        $this->assertGreaterThan($toChangeSecond, $toChangeThird);
    }

    public function testThatMediatorPassesTheSubjectDataToTheHandlers()
    {
        $toChange = '';

        $this->mediator->on('event', function ($arg1, $arg2) use (&$toChange) {
            $toChange = $arg1 . $arg2;
        });

        $this->mediator->fire('event', ['has-', 'changed']);

        $this->assertEquals('has-changed', $toChange);
    }

    public function testTHatMediatorReturnsTheHandlersResponsesWhenFiring()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->mediator->on('event', function () use ($i) {
                return $i;
            });
        }

        $response = $this->mediator->fire('event');

        $this->assertEquals([0, 1, 2, 3, 4], $response);
    }

    public function testThatMediatorAcceptsAServiceAsAHandler()
    {
        $this->container['someService'] = function () {
            return new SomeMockClass();
        };

        // default calls 'handle' method
        $this->mediator->on('event', 'someService');
        // non-default calls method after '@'
        $this->mediator->on('event', 'someService@doSomething');

        $response = $this->mediator->fire('event', ['passed-', 'by-class']);

        $this->assertEquals('passed-by-class', implode('', $response));
    }

    public function testThatMediatorCanFireWildCardsEventHandlers()
    {
        $this->mediator->on('event.subevent', function () {
            return 'fromSub';
        });

        $this->mediator->on('event.*', function () {
            return 'fromWildcard';
        });

        $response = $this->mediator->fire('event.subevent');

        $this->assertEquals(['fromSub', 'fromWildcard'], $response);
    }

    public function testThatMediatorCanReturnTHeCurrentEvent()
    {
        $event1 = 'event.subevent1';
        $event2 = 'event.subevent2';
        $event3 = 'event.subevent3';
        $this->mediator->on($event1, function () {
            return 5;
        });
        $this->mediator->on($event2, function () {
            return -5;
        });
        $this->mediator->on($event3, function () {
            return 10;
        });

        $this->mediator->on('event.*', function () use ($event1, $event2, $event3) {
            $result = [
                $event1 => 5,
                $event2 => 10,
                $event3 => 15,
            ];

            return $result[$this->mediator->current()] ?? 0;
        });

        $result = $this->mediator->fire($event1)[0];
        $result += $this->mediator->fire($event1)[1];
        $result += $this->mediator->fire($event2)[0];
        $result += $this->mediator->fire($event2)[1];
        $result += $this->mediator->fire($event3)[0];
        $result += $this->mediator->fire($event3)[1];

        $this->assertEquals(40, $result);
    }

    public function testThatMediatorStopsEventPropagationAfterAHandlerReturnsFalse()
    {
        $this->mediator->on('event', function () {
            return 5;
        });
        $this->mediator->on('event', function () {
            return -5;
        });
        $this->mediator->on('event', function () {
            return 10;
        });
        $this->mediator->on('event', function () {
            return false;
        });
        $this->mediator->on('event', function () {
            return 10;
        });

        $response = $this->mediator->fire('event');

        $this->assertEquals(10, array_sum($response));
    }

    public function testThatMediatorCanRegisterAHandlerForSeveralEventsAtOnce()
    {
        $this->mediator->on(['event1', 'event2', 'event3'], function () {
            return 5;
        });

        $result = 0;

        $result += $this->mediator->fire('event1')[0];
        $result += $this->mediator->fire('event2')[0];
        $result += $this->mediator->fire('event3')[0];

        $this->assertEquals(15, $result);
    }

    public function testThatMediatorCanAcceptSubscriberClassesAsHandlers()
    {
        $mockHandler = $this->createMock('MockHandler', ['subscribes']);
        $mockHandler->expects($this->once())
                    ->method('subscribes')
                    ->with($this->mediator);

        $this->mediator->subscribe($mockHandler);
    }
}

class MockHandler
{
    public function subscribes()
    {
        return true;
    }
}

class SomeMockClass
{
    public function __call($method, $args)
    {
        switch ($method) {
            case 'doSomething':
                return $args[1];

            case 'handle':
            default:
                return $args[0];
        }
    }
}
