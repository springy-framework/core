<?php
/**
 * Test case for Springy\HTTP\Input class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Core\Configuration;
use Springy\HTTP\Input;
use Springy\HTTP\Session;

/**
 * @runTestsInSeparateProcesses
 */
class InputTest extends TestCase
{
    public $input;

    public function setUp()
    {
        $config = Configuration::getInstance(__DIR__ . '/../conf', 'test');
        $session = Session::getInstance();
        $session->configure($config);

        $this->input = new Input();
    }

    public function testAll()
    {
        $this->assertCount(0, $this->input->all());
    }

    public function testAllFiles()
    {
        $this->assertCount(0, $this->input->allFiles());
    }

    public function testExcept()
    {
        $this->assertCount(0, $this->input->except(['id', 'name']));
    }

    public function testFile()
    {
        $this->assertNull($this->input->file('file'));
    }

    public function testGet()
    {
        $this->assertNull($this->input->get('name'));
        $this->assertEquals('bar', $this->input->get('foo', 'bar'));
    }

    public function testHas()
    {
        $this->assertFalse($this->input->has('name'));
    }

    public function testHasFile()
    {
        $this->assertFalse($this->input->hasFile('file'));
    }

    public function testIsAjax()
    {
        $this->assertFalse($this->input->isAjax());
    }

    public function testIsPost()
    {
        $this->assertFalse($this->input->isPost());
    }

    public function testOld()
    {
        $this->assertNull($this->input->old('name'));
        $this->assertEquals('bar', $this->input->old('foo', 'bar'));
    }

    public function testOnly()
    {
        $this->assertCount(0, $this->input->only(['id', 'name']));
    }

    public function testStoreForNextRequest()
    {
        $this->assertNull($this->input->storeForNextRequest());
    }
}
