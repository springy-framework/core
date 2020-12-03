<?php
/**
 * Test case for Springy\Core\Kernel class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Exceptions\Handler;

class HandlerTest extends TestCase
{
    public $handler;

    protected function setUp(): void
    {
        $this->handler = new Handler();
    }

    public function testIgnoredErrors()
    {
        $this->assertCount(0, $this->handler->getIgnoredErrors());

        $this->handler->addIgnoredError(0);
        $this->assertCount(1, $this->handler->getIgnoredErrors());
        $this->assertContains(0, $this->handler->getIgnoredErrors());

        $this->handler->addIgnoredError([200, 300]);
        $this->assertCount(3, $this->handler->getIgnoredErrors());
        $this->assertContains(200, $this->handler->getIgnoredErrors());

        $this->handler->delIgnoredError(200);
        $this->assertCount(2, $this->handler->getIgnoredErrors());
        $this->assertNotContains(200, $this->handler->getIgnoredErrors());
    }

    public function testErrorHandler()
    {
        $this->handler->addIgnoredError(200);
        $this->assertNull($this->handler->errorHandler(
            200,
            'Test error handler',
            __FILE__,
            __LINE__
        ));
    }

    public function testExceptionHandler()
    {
        $this->handler->addIgnoredError(E_USER_ERROR);
        $this->assertNull($this->handler->exceptionHandler(
            new Exception('Test exception handler', E_USER_ERROR)
        ));
    }

    public function testTrigger()
    {
        $this->assertNull($this->handler->trigger());
    }
}
