<?php

/**
 * Test case for Springy\Utils\JSON class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version    1.0.0
 */

use PHPUnit\Framework\TestCase;
use Springy\Utils\JSON;

class JSONTest extends TestCase
{
    public $data;
    public $json;

    protected function setUp(): void
    {
        $this->data = [
            'Homer'  => 'Duh!',
            'Nelson' => 'Ha ha!',
        ];
        $this->json = new JSON($this->data);
    }

    public function testAddAndGet()
    {
        $this->json->add('Homer', 'Duh!');
        $this->assertEquals($this->data, $this->json->getData());

        $this->json->add('Maggie', 'Chup chup!');
        $this->assertNotEquals($this->data, $this->json->getData());

        $newdata = ['bar' => 'Foo'];
        $this->json->add($newdata);
        $this->assertCount(4, $this->json->getData());
    }

    public function testFetch()
    {
        $jsonstr = json_encode($this->data);
        $this->assertEquals($jsonstr, $this->json->fetch());
    }

    public function testMerge()
    {
        $newdata = ['bar' => 'Foo'];

        $this->assertCount(2, $this->json->getData());
        $this->json->merge($newdata);
        $this->assertCount(3, $this->json->getData());
    }

    public function testSet()
    {
        $newdata = ['foo' => 'bar'];

        $this->json->setData($newdata);
        $this->assertEquals($newdata, $this->json->getData());
    }

    public function testHeaderStatus()
    {
        $this->assertEquals(200, $this->json->getHeaderStatus());
        $this->json->setHeaderStatus(201);
        $this->assertEquals(201, $this->json->getHeaderStatus());
    }
}
