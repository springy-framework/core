<?php
/**
 * Test case for Springy\Core\Debug class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Core\Debug;

/**
 * @runTestsInSeparateProcesses
 */
class DebugTest extends TestCase
{
    public $debug;

    protected function setUp(): void
    {
        $this->debug = Debug::getInstance();
    }

    public function testAdd()
    {
        $this->assertNull(
            $this->debug->add('Bar', false)
        );
        $this->assertNull(
            $this->debug->add('Foo', true, false)
        );
        $this->assertNull(
            $this->debug->add(['Foo', 'Bar'], true, true, false)
        );
    }

    public function testGet()
    {
        $this->debug->add('Bar', false);
        $this->debug->add('Foo', true, false);
        $this->assertStringStartsWith('> Time:', $this->debug->get('plain'));
        $this->assertStringStartsWith('{"Time":', $this->debug->get('json'));
        $this->assertStringStartsWith('<div class="springy-debug-info">', $this->debug->get('html'));
    }

    public function testGetSimpleData()
    {
        $this->debug->add('Bar', false);
        $this->debug->add('Foo', true, false);

        $this->assertCount(2, $this->debug->getSimpleData());
    }

    public function testInject()
    {
        $this->assertStringStartsWith('Bar', $this->debug->inject('Bar'));
    }
}
