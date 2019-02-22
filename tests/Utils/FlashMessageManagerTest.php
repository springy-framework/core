<?php
/**
 * Test case for Springy\Utils\FlashMessageManager class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Core\Kernel;
use Springy\Utils\FlashMessagesManager;
use Springy\Utils\MessageContainer;

/**
 * @runTestsInSeparateProcesses
 */
class FlashMessageManagerTest extends TestCase
{
    public $flash;

    public function setUp()
    {
        Kernel::getInstance()->setEnvironment('test');
        $this->flash = new FlashMessagesManager();
    }

    public function testErrors()
    {
        $this->assertInstanceOf(MessageContainer::class, $this->flash->errors());
    }

    public function testLastErrors()
    {
        $this->assertInstanceOf(MessageContainer::class, $this->flash->lastErrors());
    }

    public function testLastMessages()
    {
        $this->assertInstanceOf(MessageContainer::class, $this->flash->lastMessages());
    }

    public function testMessages()
    {
        $this->assertInstanceOf(MessageContainer::class, $this->flash->messages());
    }

    public function testSetErrors()
    {
        $container = new MessageContainer();
        $this->assertNull($this->flash->setErrors($container));
    }

    public function testSetMessages()
    {
        $container = new MessageContainer();
        $this->assertNull($this->flash->setMessages($container));
    }
}
