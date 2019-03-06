<?php
/**
 * Test case for Springy\Database\Connection class.
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

class ModelTest extends TestCase
{
    public function testSimpleCreate()
    {
        $model = new TestSpf();

        $this->assertInstanceOf(Model::class, $model);
    }

    public function testSimpleLoad()
    {
        $model = new TestSpf(1);

        $this->assertTrue($model->isLoaded());
        $this->assertEquals('Homer', $model->get('name'));
        $this->assertEquals(1, $model->id);
        $this->assertEquals(['id', 'name'], $model->key());
        $this->assertEquals([
            'id' => 1,
            'name' => 'Homer',
        ], $model->get());
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
        $model = new TestSpf(1);
        $model->setFetchAsObject(true);
        $obj = $model->get();

        $this->assertEquals(1, $obj->id);
    }
}

class TestSpf extends Model
{
    protected $table = 'test_spf';
    protected $columns = [
        'id' => [],
        'name' => [],
    ];
    protected $primaryKey = ['id'];
    protected $dbIdentity = 'mysql';
}
