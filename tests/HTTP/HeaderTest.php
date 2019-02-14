<?php
/**
 * Test case for Springy\HTTP\Header class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\HTTP\Header;

class HeaderTest extends TestCase
{
    public $header;

    public function setUp()
    {
        $this->header = new Header();
    }

    public function testCacheControl()
    {
        $this->assertNull($this->header->cacheControl('no-cache'));
    }

    public function testContentType()
    {
        $this->assertNull($this->header->contentType('text/plain'));
    }

    public function testExpires()
    {
        $this->assertNull($this->header->expires('0'));
    }

    public function testHeaders()
    {
        $this->assertEmpty($this->header->headers());
    }

    public function testHttpResponseCode()
    {
        $this->assertEquals(200, $this->header->httpResponseCode());
        $this->assertEquals(500, $this->header->httpResponseCode(500));
    }

    public function testIsEmpty()
    {
        $this->assertTrue($this->header->isEmpty());
    }

    public function testNotFound()
    {
        $this->header->notFound();
        $this->assertEquals(404, $this->header->httpResponseCode());
    }

    public function testPragma()
    {
        $this->assertNull($this->header->pragma('no-cache'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testSend()
    {
        $this->header->contentType('text/plain');
        $this->assertTrue($this->header->send());
    }
}
