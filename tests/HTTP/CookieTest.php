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

/**
 * @runTestsInSeparateProcesses
 */
class CookieTest extends TestCase
{
    protected const BAR = 'bar';
    protected const BARFOO = 'bar[foo]';
    protected const FOO = 'foo';

    public $cookie;

    protected function setUp(): void
    {
        $_COOKIE[self::FOO] = self::BAR;
        $_COOKIE[self::BAR][self::FOO] = self::FOO;

        $this->cookie = Cookie::getInstance();
    }

    public function testDelete()
    {
        $this->cookie->delete(self::FOO);
        $this->assertFalse(isset($_COOKIE[self::FOO]));
        $this->cookie->delete(self::BAR);
        $this->assertFalse(isset($_COOKIE[self::BAR][self::FOO]));
        $this->assertFalse(isset($_COOKIE[self::BAR]));
    }

    public function testExists()
    {
        $this->assertTrue($this->cookie->exists(self::FOO));
        $this->assertTrue($this->cookie->exists(self::BARFOO));
        $this->assertTrue($this->cookie->exists([self::BAR => self::FOO]));
    }

    public function testGet()
    {
        $this->assertEquals(self::BAR, $this->cookie->get(self::FOO));
        $this->assertEquals(self::FOO, $this->cookie->get(self::BARFOO));
        $this->assertEquals(self::FOO, $this->cookie->get([self::BAR => self::FOO]));
    }

    public function testSet()
    {
        $this->assertTrue($this->cookie->set(self::FOO, self::BAR));
        $this->assertTrue($this->cookie->set(self::BARFOO, self::FOO));
        $this->assertTrue($this->cookie->set([self::BAR => self::FOO], self::FOO));
    }
}
