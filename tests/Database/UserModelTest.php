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
use Springy\Database\Query\Embed;
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
        $this->assertEquals(['id', 'name', 'created', 'deleted', 'person'], $model->key());
        $this->assertEquals('Person Krusty', $model->person);
        $this->assertCount(5, $model->get());
    }

    public function testSetColumns()
    {
        $model = new TestSpf();
        $model->setColumns(['id', 'name']);
        $model->load(11);

        $this->assertTrue($model->isLoaded());
        $this->assertEquals('Krusty', $model->get('name'));
        $this->assertEquals(11, $model->id);
        $this->assertEquals(['id', 'name', 'person'], $model->key());
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
        $this->assertEquals('Person Marge', $model->person);

        $model->end();
        $this->assertEquals('Bart', $model->get('name'));
        $this->assertEquals('Person Bart', $model->person);

        $model->prev();
        $this->assertEquals('Lisa', $model->get('name'));
        $this->assertEquals('Person Lisa', $model->person);

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

    public function testValidate()
    {
        $model = new TestSpf(9);
        $model->name = '';

        $this->assertFalse($model->validate());

        $errors = $model->getValidationErrors();

        $this->assertArrayHasKey('name', $errors->getMessages());
    }

    public function testInsert()
    {
        $model = new TestSpf();
        $model->name = 'Moe';

        $this->assertTrue($model->validate());
        $this->assertEquals(null, $model->id);
        $this->assertEquals('Moe', $model->name);
        $this->assertEquals(1, $model->save());
        $this->assertEquals(12, $model->id);
    }

    public function testCount()
    {
        $model = new TestSpf();
        $where = new Where();
        $where->add('id', 2, Where::OP_GREATER_EQUAL);
        $where->add('id', 5, Where::OP_LESS_EQUAL);

        $this->assertEquals(4, $model->count($where));
    }

    public function testUpdate()
    {
        $model = new TestSpf(12);
        $model->name = 'Barney';

        $this->assertEquals(1, $model->save());
        $this->assertEquals('Barney Foo', $model->name);
        $this->assertEquals('Person Barney Foo', $model->person);
    }

    public function testDeleteCurrent()
    {
        $model = new TestSpf(11);

        $this->assertTrue($model->isLoaded());
        $this->assertEquals('Krusty', $model->name);
        $this->assertEquals(1, $model->delete());
    }

    public function testFailDeleteByTrigger()
    {
        $model = new TestSpf(5);

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

    public function testEmbed()
    {
        $model = new TestSpf();
        $mdEmb = new EmbedModel();
        $embed = new Embed('tstEmbed', $mdEmb, 'id', 'id');
        $model->addEmbed($embed);

        $rows = $model->select(new Where());

        $result = [];
        foreach ($rows as $row) {
            $result[] = $row['tstEmbed']['surname'] ?? null;
        }

        $this->assertEquals([
            'Foo',
            'Foo',
            'Foo',
            'Bar',
            'Bar',
            'Bar',
        ], $result);
    }
}

class TestSpf extends Model
{
    protected $table = 'test_spf';
    protected $dbIdentity = 'mysql';
    protected $abortOnEmptyFilter = false;

    protected function myHook($value)
    {
        return $value === '' ? '' : $value.($this->newRecord ? '' : ' Foo');
    }

    protected function person($row)
    {
        return 'Person '.$row['name'];
    }

    protected function triggerBeforeDelete()
    {
        return $this->name !== 'Meggy';
    }
}

class EmbedModel extends Model
{
    protected $table = 'test_embed';
    protected $dbIdentity = 'mysql';
}
