<?php
/**
 * Test case for Springy\Database\Model class.
 *
 * This test was named UserModelTest to be executed after all other
 * Database tests.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Database\Model;
use Springy\Database\Query\Where;

class UserModelTest extends TestCase
{
    public function testSimpleCreate()
    {
        $model = new TestSpf();

        $this->assertInstanceOf(Model::class, $model);
        $this->assertEquals(['id'], $model->getPKColumns());
    }

    public function testSimpleLoad()
    {
        $model = new TestSpf(11);

        $this->assertTrue($model->isLoaded());
        $this->assertEquals('Krusty', $model->get('name'));
        $this->assertEquals(11, $model->id);
        $this->assertEquals(['id', 'name', 'created', 'deleted'], $model->key());
        $this->assertCount(4, $model->get());
    }

    public function testModelNavigation()
    {
        $model = new TestSpf();
        $where = new Where();
        $where->add('id', 2, Where::OP_GREATER_EQUAL);
        $where->add('id', 4, Where::OP_LESS_EQUAL);
        $model->select($where);

        $this->assertTrue($model->valid());
        $this->assertEquals('Marge', $model->get('name'));

        $model->end();
        $this->assertEquals('Bart', $model->get('name'));

        $model->prev();
        $this->assertEquals('Lisa', $model->get('name'));

        $model->rewind();
        $this->assertEquals('Marge', $model->get('name'));

        $model->next();
        $this->assertEquals('Lisa', $model->get('name'));
    }

    public function testFetchAsObject()
    {
        $model = new TestSpf(11);
        $model->setFetchAsObject(true);
        $obj = $model->get();

        $this->assertEquals('Krusty', $obj->name);
    }

    public function testSetValue()
    {
        $model = new TestSpf(11);
        $model->name = 'Clow';

        $this->assertEquals('Clow Foo', $model->name);
    }

    public function testInsert()
    {
        $model = new TestSpf();
        $model->name = 'Moe';

        $this->assertEquals(null, $model->id);
        $this->assertEquals('Moe', $model->name);
        $this->assertEquals(1, $model->save());
        $this->assertEquals(12, $model->id);
    }

    public function testUpdate()
    {
        $model = new TestSpf(12);
        $model->name = 'Barney';

        $this->assertEquals(1, $model->save());
        $this->assertEquals('Barney Foo', $model->name);
    }

    public function testDeleteCurrent()
    {
        $model = new TestSpf(11);

        $this->assertEquals(1, $model->delete());

        $model->load(5);
        $this->assertTrue($model->isLoaded());
        $this->assertEquals('Meggy', $model->name);
        $this->assertEquals(0, $model->delete());
    }

    public function testDeleteByPK()
    {
        $model = new TestSpf();

        $this->assertEquals(1, $model->delete(8));
    }

    public function testDeleteByWhere()
    {
        $model = new TestSpf();
        $where = new Where();
        $where->add('name', ['Lisa', 'Bart', 'Meggy'], Where::OP_IN);

        $this->assertEquals(2, $model->delete($where));
    }
}

class TestSpf extends Model
{
    protected $table = 'test_spf';
    protected $columns = [
        'id' => [
            'pk' => true,
            'readonly' => true,
        ],
        'name' => [
            'type' => 'string',
            'hook' => 'myHook',
        ],
        'created' => [
            'ad' => true,
        ],
        'deleted' => [
            'sd' => true,
        ]
    ];
    protected $dbIdentity = 'mysql';

    protected function myHook($value)
    {
        return $value.($this->newRecord ? '' : ' Foo');
    }

    protected function triggerBeforeDelete()
    {
        return ($this->name !== 'Meggy');
    }
}
