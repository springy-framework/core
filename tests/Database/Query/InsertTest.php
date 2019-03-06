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
use Springy\Exceptions\SpringyException;
use Springy\Database\Query\Value;

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

    public function testComplesInsert()
    {
        $this->insert->addValue('name', 'Apu');
        $this->insert->addValue('inserted_at', 'NOW()', true);

        $sql = 'INSERT INTO test(name, inserted_at) VALUES (?, NOW())';
        $this->assertEquals($sql, (string) $this->insert);
        $this->assertEquals(['Apu'], $this->insert->params());
    }

    public function testMultiInsert()
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
