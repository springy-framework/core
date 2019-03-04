<?php
/**
 * Test case for Springy\Database\Select class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Database\Connection;
use Springy\Database\Select;
use Springy\Database\Where;
use Springy\Exceptions\SpringyException;
use Springy\Database\Join;

class SelectTest extends TestCase
{
    public $select;

    public function setUp()
    {
        $connection = new Connection('mysql');
        $this->select = new Select($connection, 'test');
    }

    public function testEmptyColumns()
    {
        $this->expectException(SpringyException::class);
        $this->select->parse();
    }

    public function testSimpleSelect()
    {
        $this->select->addColumn('column1');

        $sql = 'SELECT test.column1 FROM test';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testSelectWithOrderBy()
    {
        $this->select->addColumn('column1');
        $this->select->addColumn('column2');
        $this->select->addOrderBy('column1');
        $this->select->addOrderBy('column2', Select::ORDER_DESC);

        $sql = 'SELECT test.column1, test.column2 FROM test ORDER BY column1, column2 DESC';
        $this->assertEquals($sql, (string) $this->select);

        $this->select->setOrderBy(['column2' => Select::ORDER_ASC]);
        $sql = 'SELECT test.column1, test.column2 FROM test ORDER BY column2';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testSelectWithOffset()
    {
        $this->select->addColumn('column1');
        $this->select->setOffset(1).

        $sql = 'SELECT test.column1 FROM test OFFSET 1';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testSelectWithLimit()
    {
        $this->select->addColumn('column1');
        $this->select->setLimit(10).

        $sql = 'SELECT test.column1 FROM test LIMIT 10';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testSelectWithLimitOffset()
    {
        $this->select->addColumn('column1');
        $this->select->setLimit(10).
        $this->select->setOffset(1).

        $sql = 'SELECT test.column1 FROM test LIMIT 10 OFFSET 1';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testSelectWithAliasForTableName()
    {
        $this->select->setTable('test', 't');
        $this->select->addColumn('column1');

        $sql = 'SELECT t.column1 FROM test AS t';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testSelectWithAliasForFieldName()
    {
        $this->select->addColumn('column1', 'the_col');

        $sql = 'SELECT test.column1 AS the_col FROM test';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testSelectCountSumAndOtherFunctions()
    {
        $this->select->addCount('0', 'qtty');
        $this->select->addMax('column1', 'major');
        $this->select->addMin('column1', 'minor');
        $this->select->addSum('column1', 'total');
        $this->select->addFunction('AVG(column1)', '`average`');

        $sql = 'SELECT COUNT(0) AS qtty, MAX(column1) AS major, MIN(column1) AS minor, SUM(column1) AS total, AVG(column1) AS `average` FROM test';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testSelectGroup()
    {
        $this->select->addColumn('column1');
        $this->select->addSum('0', 'quantity');
        $this->select->addGroupBy('column1');

        $sql = 'SELECT test.column1, SUM(0) AS quantity FROM test GROUP BY column1';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testSelectGroupAndHaving()
    {
        $this->select->addColumn('column1');
        $this->select->addSum('0', 'quantity');
        $this->select->addGroupBy('column1');
        $this->select->addHaving('quantity', 1, Where::OP_GREATER);

        $sql = 'SELECT test.column1, SUM(0) AS quantity FROM test GROUP BY column1 HAVING quantity > ?';
        $this->assertEquals($sql, (string) $this->select);
        $this->assertEquals([1], $this->select->params());
    }

    public function testSelectWithJoin()
    {
        $this->select->addColumn('id');

        $join = new Join('table2');
        $join->addOnColumns('table2.id', 'test.foreign_id');
        $this->select->addJoin($join);

        $sql = 'SELECT test.id FROM test INNER JOIN table2 ON table2.id = test.foreign_id';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testSelectWithJoinWithColumns()
    {
        $this->select->addColumn('id');

        $join = new Join('table2', Join::LEFT_OUTER);
        $join->addOnColumns('table2.id', 'test.foreign_id');
        $join->addColumn('col3');
        $this->select->addJoin($join);

        $this->select->addColumn('foo');

        $sql = 'SELECT test.id, table2.col3, test.foo FROM test LEFT OUTER JOIN table2 ON table2.id = test.foreign_id';
        $this->assertEquals($sql, (string) $this->select);
    }

    public function testWithWhere()
    {
        $this->select->addSum('column1', 'quantity');

        $where = new Where();
        $where->add('column1', 3, Where::OP_GREATER);
        $this->select->setWhere($where);

        $sql = 'SELECT SUM(column1) AS quantity FROM test WHERE column1 > ?';
        $this->assertEquals($sql, (string) $this->select);
        $this->assertEquals([3], $this->select->params());
    }
}