<?php
/**
 * Test case for Springy\HTTP\Request class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\HTTP\Request;

class RequestTest extends TestCase
{
    public $request;

    public function setUp()
    {
        $this->request = Request::getInstance();
    }

    public function testIsAjax()
    {
        $this->assertFalse($this->request->isAjax());
    }

    public function testIsGet()
    {
        $this->assertFalse($this->request->isGet());
    }

    public function testIsPost()
    {
        $this->assertFalse($this->request->isPost());
    }

    public function testMethod()
    {
        $this->assertEmpty($this->request->method());
    }
}
