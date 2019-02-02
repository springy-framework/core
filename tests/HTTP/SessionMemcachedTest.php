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

class SessionMemcachedTest extends TestCase
{
    public $session;
    public $config;

    public function setUp()
    {
        $this->session = Session::getInstance();
        $this->config = new Configuration(__DIR__.'/../conf', 'test', 'memcached');
    }

    /**
     * @runInSeparateProcess
     */
    public function testConfigure()
    {
        $this->assertInstanceOf(
            Springy\HTTP\Session::class,
            $this->session->configure($this->config)
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testDefined()
    {
        $this->session->configure($this->config);
        $this->assertFalse($this->session->defined('foo'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testGet()
    {
        $this->session->configure($this->config);
        $this->assertNull($this->session->get('foo'));
        $this->assertEquals('foo', $this->session->get('bar', 'foo'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetId()
    {
        $this->session->configure($this->config);
        $this->assertRegExp('/^[A-Za-z0-9\-]+$/', $this->session->getId());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSet()
    {
        $this->session->configure($this->config);
        $this->assertNull($this->session->set('foo', 'bar'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetId()
    {
        $this->session->configure($this->config);
        $this->assertNull($this->session->setId(substr(md5(uniqid(mt_rand(), true)), 0, 26)));
    }

    /**
     * @runInSeparateProcess
     */
    public function testStart()
    {
        $this->session->configure($this->config);
        $this->assertTrue($this->session->start());
    }

    /**
     * @runInSeparateProcess
     */
    public function testUnset()
    {
        $this->session->configure($this->config);
        $this->assertNull($this->session->unset('foo'));
    }
}
