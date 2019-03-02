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
use Springy\Database\Connection;

class ConnectionTest extends TestCase
{
    public function testConnectionMySQL()
    {
        $connection = new Connection('mysql');
        $this->assertTrue($connection->isConnected());

        $connection->run('CREATE TABLE IF NOT EXISTS test_spf(column1 INT)');
        $connection->run('TRUNCATE TABLE test_spf');
        $connection->run('INSERT INTO test_spf(column1) VALUES (1), (2), (3), (4), (5), (6)');
        $result = $connection->select('SELECT column1 FROM test_spf WHERE column1 BETWEEN ? AND ?', [2, 4]);
        $this->assertCount(3, $result);

        $this->assertEquals(3, $connection->affectedRows());

        $row = $connection->fetchFirst();
        $this->assertEquals(2, $row['column1'] ?? null);

        $row = $connection->fetchNext();
        $this->assertEquals(2, $row['column1'] ?? null);

        $row = $connection->fetchLast();
        $this->assertEquals(4, $row['column1'] ?? null);

        $row = $connection->fetchPrev();
        $this->assertEquals(3, $row['column1'] ?? null);

        $row = $connection->fetchCurrent();
        $this->assertEquals(3, $row['column1'] ?? null);

        $connection->disconnect();
        $this->assertFalse($connection->isConnected());
    }

    public function testConnectionPostgres()
    {
        $connection = new Connection('postgres');
        $this->assertTrue($connection->isConnected());

        $connection->disconnect();
        $this->assertFalse($connection->isConnected());
    }

    public function testConnectionSQLite()
    {
        $connection = new Connection('sqlite');
        $this->assertTrue($connection->isConnected());

        $connection->run('CREATE TABLE test(column1 int)');
        $connection->run('INSERT INTO test(column1) VALUES (1), (2), (3), (4), (5), (6)');
        $result = $connection->select('SELECT column1 FROM test WHERE column1 BETWEEN 2 AND 4');
        $this->assertCount(3, $result);
    }

    public function testMySqlConnectionWithFileRoundRobin()
    {
        $connection = new Connection('mysql_file');
        $this->assertTrue($connection->isConnected());

        $connection->disconnect();
        $this->assertFalse($connection->isConnected());
    }

    public function testMySqlConnectionWithMemcachedRoundRobin()
    {
        $connection = new Connection('mysql_mc');
        $this->assertTrue($connection->isConnected());

        $connection->disconnect();
        $this->assertFalse($connection->isConnected());
    }
}
