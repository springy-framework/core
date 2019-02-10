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
use Springy\Core\Configuration;
use Springy\Core\Debug;

/**
 * @runTestsInSeparateProcesses
 */
class DebugTest extends TestCase
{
    public $debug;

    public function setUp()
    {
        new Configuration(__DIR__.'/../conf', 'test');

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
        $this->assertStringStartsWith('- Alocated memory', $this->debug->get());
    }

    public function testHighlight()
    {
        $intVar = 0;
        $stringVar = 'Foo';
        $arrayVar = ['Foo', 'Bar'];

        $this->assertEquals('0', $this->debug->highligh($intVar));
        $this->assertEquals('Foo', $this->debug->highligh($stringVar));
        $this->assertStringStartsWith('Array', $this->debug->highligh($arrayVar));
    }

    public function testInject()
    {
        $this->assertStringStartsWith('Foo', $this->debug->inject('Bar'));
    }
}
