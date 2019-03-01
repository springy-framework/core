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
        $connection->run('SELECT column1 FROM test_spf WHERE column1 BETWEEN 2 AND 4');
        $this->assertCount(3, $connection->fetchAll());

        $connection->disconnect();
        $this->assertFalse($connection->isConnected());
    }

    public function testConnectionSQLite()
    {
        $connection = new Connection('sqlite');
        $this->assertTrue($connection->isConnected());

        $connection->run('CREATE TABLE test(column1 int)');
        $connection->run('INSERT INTO test(column1) VALUES (1), (2), (3), (4), (5), (6)');
        $connection->run('SELECT column1 FROM test WHERE column1 BETWEEN 2 AND 4');
        $this->assertCount(3, $connection->fetchAll());

        $connection->disconnect();
        $this->assertFalse($connection->isConnected());
    }
}
