<?php
/**
 * Test case for Springy\Database\Query\Insert class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Database\Connection;
use Springy\Database\Query\Insert;
use Springy\Database\Query\Value;
use Springy\Exceptions\SpringyException;

class InsertTest extends TestCase
{
    public $insert;

    public function setUp()
    {
        $connection = new Connection('mysql');
        $this->insert = new Insert($connection, 'test');
    }

    public function testEmptyColumns()
    {
        $this->expectException(SpringyException::class);
        $this->insert->parse();
    }

    public function testSimpleInsert()
    {
        $this->insert->addValue('name', 'Apu');

        $sql = 'INSERT INTO test(name) VALUES (?)';
        $this->assertEquals($sql, (string) $this->insert);
        $this->assertEquals(['Apu'], $this->insert->params());
    }

    public function testPriorityInsert()
    {
        $this->insert->addValue('name', 'Apu');
        $this->insert->setPriority(Insert::MYSQL_HIGH_PRIORITY);

        $sql = 'INSERT HIGH_PRIORITY INTO test(name) VALUES (?)';
        $this->assertEquals($sql, (string) $this->insert);
        $this->assertEquals(['Apu'], $this->insert->params());
    }

    public function testInsertIgnore()
    {
        $this->insert->addValue('name', 'Apu');
        $this->insert->setIgnoreError(true);

        $sql = 'INSERT IGNORE INTO test(name) VALUES (?)';
        $this->assertEquals($sql, (string) $this->insert);

        $this->insert->setPriority(Insert::MYSQL_LOW_PRIORITY);
        $sql = 'INSERT LOW_PRIORITY IGNORE INTO test(name) VALUES (?)';
    }

    public function testInsertIgnoreSqlite()
    {
        $connection = new Connection('sqlite');
        $this->insert = new Insert($connection, 'test');
        $this->insert->addValue('name', 'Apu');
        $this->insert->setIgnoreError(true);

        $sql = 'INSERT OR IGNORE INTO test(name) VALUES (?)';
        $this->assertEquals($sql, (string) $this->insert);
    }

    public function testInsertIgnorePostgre()
    {
        $connection = new Connection('postgres');
        $this->insert = new Insert($connection, 'test');
        $this->insert->addValue('name', 'Apu');
        $this->insert->setIgnoreError(true);

        $sql = 'INSERT INTO test(name) VALUES (?) ON CONFLICT DO NOTHING';
        $this->assertEquals($sql, (string) $this->insert);
    }

    public function testComplexInsert()
    {
        $this->insert->addValue('name', 'Apu');
        $this->insert->addValue('inserted_at', 'NOW()', true);

        $sql = 'INSERT INTO test(name, inserted_at) VALUES (?, NOW())';
        $this->assertEquals($sql, (string) $this->insert);
        $this->assertEquals(['Apu'], $this->insert->params());
    }

    public function testMultiRowInsert()
    {
        $this->insert->addValues([
            new Value('name', 'Apu'),
        ]);

        $this->insert->addValues([
            new Value('name', 'Nelson'),
        ]);

        $sql = 'INSERT INTO test(name) VALUES (?), (?)';
        $this->assertEquals($sql, (string) $this->insert);
        $this->assertEquals(['Apu', 'Nelson'], $this->insert->params());
    }
}
