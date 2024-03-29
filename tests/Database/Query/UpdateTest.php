<?php
/**
 * Test case for Springy\Database\Query\Update class.
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
use Springy\Database\Query\Update;
use Springy\Exceptions\SpringyException;

class UpdateTest extends TestCase
{
    protected const HOMER = 'Homer Simpson';

    /** @var Update */
    public $update;

    protected function setUp(): void
    {
        $connection = new Connection('mysql');
        $this->update = new Update($connection, 'test_spf');
    }

    public function testUpdateWithoutWhere()
    {
        $this->update->addValue('name', self::HOMER);

        $this->expectException(SpringyException::class);
        $this->update->parse();
    }

    public function testUpdateWithoutSafeMode()
    {
        $this->update->addValue('name', self::HOMER);
        $this->update->setSafeMode(false);

        $sql = 'UPDATE test_spf SET name = ?';
        $this->assertEquals($sql, (string) $this->update);
        $this->assertEquals([self::HOMER], $this->update->params());
    }

    public function testSimpleUpdate()
    {
        $this->update->addValue('name', self::HOMER);
        $this->update->addCondition('id', 1);

        $sql = 'UPDATE test_spf SET name = ? WHERE id = ?';
        $this->assertEquals($sql, $this->update->parse());
        $this->assertEquals([self::HOMER, 1], $this->update->params());
    }

    public function testUpdateWithJoin()
    {
        $join = new Join('table2');
        $join->setAlias('t2');
        $join->addOnColumns('t2.id', 't1.id');
        $this->update->addJoin($join);

        $this->update->setAlias('t1');
        $this->update->addValue('t1.name', 't2.surname', true);
        $this->update->addCondition('t1.id', 1);

        $sql = 'UPDATE test_spf AS t1 INNER JOIN table2 AS t2 ON t2.id = t1.id SET t1.name = t2.surname WHERE t1.id = ?';
        $this->assertEquals($sql, (string) $this->update);
    }

    public function testRun()
    {
        $this->update->addValue('name', 'Ape');
        $this->update->addCondition('id', 6);

        $rows = $this->update->execute();
        $this->assertEquals(1, $rows);
    }
}
