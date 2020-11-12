<?php
/**
 * Test case for Springy\Database\Query\Select class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Database\Connection;
use Springy\Database\Query\Join;
use Springy\Database\Query\Select;
use Springy\Database\Query\Where;
use Springy\Exceptions\SpringyException;

class SelectTest extends TestCase
{
    public $select;

    public function setUp()
    {
        $connection = new Connection('mysql');
        $this->select = new Select($connection, 'test_spf');
    }

    public function testEmptyColumns()
    {
        $this->expectException(SpringyException::class);
        $this->select->parse();
    }

    public function testSimpleSelect()
    {
        $this->select->addColumn('name');

        $sql = 'SELECT test_spf.name FROM test_spf';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testSelectWithOrderBy()
    {
        $this->select->addColumn('id');
        $this->select->addColumn('name');
        $this->select->addOrderBy('name');
        $this->select->addOrderBy('id', Select::ORDER_DESC);

        $sql = 'SELECT test_spf.id, test_spf.name FROM test_spf ORDER BY name, id DESC';
        $this->assertEquals($sql, (string) $this->select);

        $this->select->setOrderBy(['name' => Select::ORDER_ASC]);
        $sql = 'SELECT test_spf.id, test_spf.name FROM test_spf ORDER BY name';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testSelectWithOffset()
    {
        $this->select->addColumn('name');
        $this->select->setOffset(1).

        $sql = 'SELECT test_spf.name FROM test_spf OFFSET 1';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testSelectWithLimit()
    {
        $this->select->addColumn('id');
        $this->select->setLimit(10);

        $sql = 'SELECT test_spf.id FROM test_spf LIMIT 10';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testSelectWithLimitOffset()
    {
        $this->select->addColumn('name');
        $this->select->setLimit(10);
        $this->select->setOffset(1);

        $sql = 'SELECT test_spf.name FROM test_spf LIMIT 10 OFFSET 1';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testSelectWithAliasForTableName()
    {
        $this->select->setTable('test_spf', 't');
        $this->select->addColumn('id');
        $this->select->addColumn('name');

        $sql = 'SELECT t.id, t.name FROM test_spf AS t';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testSelectWithAliasForFieldName()
    {
        $this->select->addColumn('name', 'person');

        $sql = 'SELECT test_spf.name AS person FROM test_spf';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testSelectCountSumAndOtherFunctions()
    {
        $this->select->addCount('0', 'qtty');
        $this->select->addMax('column1', 'major');
        $this->select->addMin('column1', 'minor');
        $this->select->addSum('column1', 'total');
        $this->select->addFunction('AVG(column1)', '`average`');

        $sql = 'SELECT COUNT(0) AS qtty, MAX(column1) AS major, MIN(column1) AS minor, SUM(column1) AS total, AVG(column1) AS `average` FROM test_spf';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testSelectGroup()
    {
        $this->select->addColumn('name');
        $this->select->addSum('0', 'quantity');
        $this->select->addGroupBy('name');

        $sql = 'SELECT test_spf.name, SUM(0) AS quantity FROM test_spf GROUP BY name';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testSelectGroupAndHaving()
    {
        $this->select->addColumn('id');
        $this->select->addSum('0', 'quantity');
        $this->select->addGroupBy('id');
        $this->select->addHaving('quantity', 1, Where::OP_GREATER);

        $sql = 'SELECT test_spf.id, SUM(0) AS quantity FROM test_spf GROUP BY id HAVING quantity > ?';
        $this->assertEquals($sql, (string) $this->select);
        $this->assertEquals([1], $this->select->params());
    }

    public function testSelectWithJoin()
    {
        $this->select->addColumn('id');

        $join = new Join('table2');
        $join->addOnColumns('table2.id', 'test_spf.id');
        $this->select->addJoin($join);

        $sql = 'SELECT test_spf.id FROM test_spf INNER JOIN table2 ON table2.id = test_spf.id';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testSelectWithJoinWithColumns()
    {
        $this->select->addColumn('id');

        $join = new Join('table2', Join::LEFT_OUTER);
        $join->addOnColumns('table2.id', 'test_spf.id');
        $join->addColumn('surname');
        $this->select->addJoin($join);

        $this->select->addColumn('name');

        $sql = 'SELECT test_spf.id, table2.surname, test_spf.name FROM test_spf LEFT OUTER JOIN table2 ON table2.id = test_spf.id';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testWithWhere()
    {
        $this->select->addSum('0', 'quantity');

        $where = new Where();
        $where->add('id', 3, Where::OP_GREATER);
        $this->select->setWhere($where);

        $sql = 'SELECT SUM(0) AS quantity FROM test_spf WHERE id > ?';
        $this->assertEquals($sql, (string) $this->select);
        $this->assertEquals([3], $this->select->params());
    }

    public function testRun()
    {
        $this->select->addColumn('name');

        $where = new Where();
        $where->add('id', 2, Where::OP_GREATER_EQUAL);
        $where->add('id', 4, Where::OP_LESS_EQUAL);
        $this->select->setWhere($where);

        $rows = $this->select->run();
        $this->assertEquals([
            ['name' => 'Marge'],
            ['name' => 'Lisa'],
            ['name' => 'Bart'],
        ], $rows);
    }
}
