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
use Springy\HTTP\Response;

class ResponseTest extends TestCase
{
    public $response;

    protected function setUp(): void
    {
        $this->response = Response::getInstance();
    }

    public function testBody()
    {
        $this->assertEmpty($this->response->body());
        $this->assertEquals('Hello world!', $this->response->body('Hello world!'));
    }

    public function testHeader()
    {
        $this->assertInstanceOf(Springy\HTTP\Header::class, $this->response->header());
    }
}
