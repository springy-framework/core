<?php
/**
 * Test case for Springy\HTTP\Session class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Core\Configuration;
use Springy\HTTP\Session;

/**
 * @runTestsInSeparateProcesses
 */
class SessionTest extends TestCase
{
    public $session;

    public function setUp()
    {
        $config = Configuration::getInstance(__DIR__.'/../conf', 'test');
        $this->session = Session::getInstance();
        $this->session->configure($config);
    }

    public function testDefined()
    {
        $this->assertFalse($this->session->defined('foo'));
    }

    public function testForget()
    {
        $this->assertNull($this->session->forget('foo'));
    }

    public function testGet()
    {
        $this->assertNull($this->session->get('foo'));
        $this->assertEquals('foo', $this->session->get('bar', 'foo'));
    }

    public function testGetId()
    {
        $this->assertRegExp('/^[A-Za-z0-9\-]+$/', $this->session->getId());
    }

    public function testSet()
    {
        $this->assertNull($this->session->set('foo', 'bar'));
    }

    public function testSetId()
    {
        $this->assertNull($this->session->setId(substr(md5(uniqid(mt_rand(), true)), 0, 26)));
    }

    public function testStart()
    {
        $this->assertTrue($this->session->start());
    }
}
