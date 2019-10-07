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

    public function testGetUriString()
    {
        $this->assertEmpty($this->uri->getUriString());
    }

    public function testGetHost()
    {
        $this->assertEquals('$', $this->uri->getHost());
    }

    public function testGetUrl()
    {
        $this->assertEquals('http://$', $this->uri->getUrl());
    }
}
