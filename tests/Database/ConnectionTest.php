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
        $this->assertEquals('`test`', $connection->enclose('test'));
        $this->assertEquals('*', $connection->enclose('*'));

        $sql = 'CREATE TABLE IF NOT EXISTS `test_spf` ('
            .'`id` INT NOT NULL AUTO_INCREMENT, '
            .'`name` VARCHAR(20) NULL, '
            .'PRIMARY KEY (`id`))';

        $connection->run($sql);
        $connection->run('TRUNCATE TABLE test_spf');
        $connection->run('INSERT INTO test_spf(`name`) VALUES (\'Homer\'), (\'Marge\'), (\'Lisa\'), (\'Bart\'), (\'Maggie\'), (\'Santa\'\'s Helper\')');
        $result = $connection->select('SELECT `id`, `name` FROM test_spf WHERE `id` BETWEEN ? AND ? ORDER BY `id`', [2, 5]);
        $this->assertCount(4, $result);

        $this->assertEquals(4, $connection->affectedRows());

        $row = $connection->fetchFirst();
        $this->assertEquals('Marge', $row['name'] ?? null);

        $row = $connection->fetchNext();
        $this->assertEquals('Lisa', $row['name'] ?? null);

        $row = $connection->fetchPrev();
        $this->assertEquals('Marge', $row['name'] ?? null);

        $row = $connection->fetch();
        $this->assertEquals(2, $row['id'] ?? null);

        $row = $connection->fetchLast();
        $this->assertEquals('Maggie', $row['name'] ?? null);

        $row = $connection->fetchCurrent();
        $this->assertEquals(5, $row['id'] ?? null);
    }

    public function testMySqlConnectionWithFileRoundRobin()
    {
        $connection = new Connection('mysql_file');
        $this->assertTrue($connection->isConnected());
    }

    public function testMySqlConnectionWithMemcachedRoundRobin()
    {
        $connection = new Connection('mysql_mc');
        $this->assertTrue($connection->isConnected());
    }

    public function testConnectionPostgres()
    {
        $connection = new Connection('postgres');
        $this->assertTrue($connection->isConnected());
        $this->assertEquals('"test"', $connection->enclose('test'));
    }

    public function testConnectionSQLite()
    {
        $connection = new Connection('sqlite');
        $this->assertTrue($connection->isConnected());
        $this->assertEquals('"test"', $connection->enclose('test'));

        $connection->run('CREATE TABLE test(column1 int)');
        $connection->run('INSERT INTO test(column1) VALUES (1), (2), (3), (4), (5), (6)');
        $result = $connection->select('SELECT column1 FROM test WHERE column1 BETWEEN 2 AND 4');
        $this->assertCount(3, $result);
    }
}
