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

    public function setUp()
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

        $data = ['bar' => 'Foo'];
        $this->json->add($data);
        $this->assertCount(4, $this->json->getData());
    }

    public function testFetch()
    {
        $json = json_encode($this->data);
        $this->assertEquals($json, $this->json->fetch());
    }

    public function testMerge()
    {
        $data = ['bar' => 'Foo'];

        $this->assertCount(2, $this->json->getData());
        $this->json->merge($data);
        $this->assertCount(3, $this->json->getData());
    }

    public function testSet()
    {
        $data = ['foo' => 'bar'];

        $this->json->setData($data);
        $this->assertEquals($data, $this->json->getData());
    }

    public function testHeaderStatus()
    {
        $this->assertEquals(200, $this->json->getHeaderStatus());
        $this->json->setHeaderStatus(201);
        $this->assertEquals(201, $this->json->getHeaderStatus());
    }
}
