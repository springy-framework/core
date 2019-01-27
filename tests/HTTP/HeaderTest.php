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

    /**
     * @runInSeparateProcess
     */
    public function testCacheControl()
    {
        $this->assertTrue($this->header->cacheControl('no-cache'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testExpires()
    {
        $this->assertTrue($this->header->expires('0'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testPragma()
    {
        $this->assertTrue($this->header->pragma('no-cache'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testContentType()
    {
        $this->assertTrue($this->header->contentType('text/plain'));
    }
}
