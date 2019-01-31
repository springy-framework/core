<?php
/**
 * Test case for Springy\HTTP\Cookie class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\HTTP\Cookie;

class CookieTest extends TestCase
{
    public $cookie;

    public function setUp()
    {
        $_COOKIE['foo'] = 'bar';
        $_COOKIE['bar']['foo'] = 'foo';

        $this->cookie = Cookie::getInstance();
    }

    /**
     * @runInSeparateProcess
     */
    public function testDelete()
    {
        $this->cookie->delete('foo');
        $this->assertFalse(isset($_COOKIE['foo']));
        $this->cookie->delete('bar');
        $this->assertFalse(isset($_COOKIE['bar']['foo']));
        $this->assertFalse(isset($_COOKIE['bar']));
    }

    public function testExists()
    {
        $this->assertTrue($this->cookie->exists('foo'));
        $this->assertTrue($this->cookie->exists('bar[foo]'));
        $this->assertTrue($this->cookie->exists(['bar' => 'foo']));
    }

    public function testGet()
    {
        $this->assertEquals('bar', $this->cookie->get('foo'));
        $this->assertEquals('foo', $this->cookie->get('bar[foo]'));
        $this->assertEquals('foo', $this->cookie->get(['bar' => 'foo']));
    }

    /**
     * @runInSeparateProcess
     */
    public function testSet()
    {
        $this->assertTrue($this->cookie->set('foo', 'bar'));
        $this->assertTrue($this->cookie->set('bar[foo]', 'foo'));
        $this->assertTrue($this->cookie->set(['bar' => 'foo'], 'foo'));
    }
}
