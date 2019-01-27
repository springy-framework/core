<?php
/**
 * Test case for Springy\HTTP\URI class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\HTTP\URI;

class URITest extends TestCase
{
    public $uri;

    public function setUp()
    {
        $this->uri = URI::getInstance();
    }

    public function testGetSegments()
    {
        $this->assertCount(0, $this->uri->getSegments());
    }

    public function testGetURIString()
    {
        $this->assertEmpty($this->uri->getURIString());
    }

    public function testHost()
    {
        $this->assertEquals('cli', $this->uri->host());
    }
}
