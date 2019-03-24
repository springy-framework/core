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
    public function testRequestOnCli()
    {
        $request = Request::getInstance();

        $this->assertEmpty($request->getMethod());
        $this->assertNull($request->getBody());
        $this->assertFalse($request->isAjax());
        $this->assertFalse($request->isDelete());
        $this->assertFalse($request->isGet());
        $this->assertFalse($request->isHead());
        $this->assertFalse($request->isOptions());
        $this->assertFalse($request->isPatch());
        $this->assertFalse($request->isPost());
        $this->assertFalse($request->isPut());
    }
}
