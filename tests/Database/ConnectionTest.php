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

        $sql = 'DROP TABLE IF EXISTS `_migration_control`';

        $connection->run($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS `test_spf` ('
            .'`id` INT NOT NULL AUTO_INCREMENT, '
            .'`name` VARCHAR(20) NULL, '
            .'`created` DATETIME NOT NULL, '
            .'`deleted` TINYINT(1) NOT NULL DEFAULT \'0\', '
            .'PRIMARY KEY (`id`))';

        $connection->run($sql);
        $connection->run('TRUNCATE TABLE `test_spf`');

        $result = $connection->insert(
            'INSERT INTO `test_spf`(`name`,`created`) VALUES (?, NOW()), (?, NOW()), (?, NOW()), (?, NOW()), (?, NOW()), (?, NOW()), (?, NOW())',
            ['Homer', 'Marge', 'Lisa', 'Bart', 'Meggy', 'Santa\'\'s Helper', 'Cat']
        );
        $this->assertEquals(7, $result);

        $result = $connection->select('SELECT `id`, `name` FROM `test_spf` WHERE `id` BETWEEN ? AND ? ORDER BY `id`', [2, 5]);
        $this->assertCount(4, $result);
        $this->assertEquals(4, $connection->affectedRows());

        $row = $connection->getFirst();
        $this->assertEquals('Marge', $row['name'] ?? null);

        $row = $connection->getNext();
        $this->assertEquals('Lisa', $row['name'] ?? null);

        $row = $connection->getPrev();
        $this->assertEquals('Marge', $row['name'] ?? null);

        $row = $connection->fetch();
        $this->assertEquals(2, $row['id'] ?? null);

        $row = $connection->getLast();
        $this->assertEquals('Meggy', $row['name'] ?? null);

        $row = $connection->getCurrent();
        $this->assertEquals(5, $row['id'] ?? null);

        $result = $connection->update('UPDATE `test_spf` SET `name` = ? WHERE `id` = ?', ['Grampa', 6]);
        $this->assertEquals(1, $result);

        $result = $connection->delete('DELETE FROM `test_spf` WHERE `id` = ?', [7]);
        $this->assertEquals(1, $result);
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

        $sql = 'CREATE TABLE IF NOT EXISTS public.test_spf ('
            .'"id" integer NOT NULL DEFAULT nextval(\'test_spf_id_seq\'::regclass),'
            .'"name" character varying(20) NOT NULL,'
            .'"created" timestamp without time zone NOT NULL,'
            .'"deleted" SMALLINT NOT NULL DEFAULT \'0\'::smallint,'
            .'CONSTRAINT test_spf_pkey PRIMARY KEY ("id")'
            .') WITH (OIDS=FALSE);';

        $connection->run($sql);
        $connection->run('TRUNCATE TABLE test_spf');
        $connection->run('ALTER SEQUENCE IF EXISTS test_spf_id_seq RESTART WITH 1');

        $result = $connection->insert(
            'INSERT INTO test_spf("name","created") VALUES '
            .'(?, CURRENT_TIMESTAMP), (?, CURRENT_TIMESTAMP), '
            .'(?, CURRENT_TIMESTAMP), (?, CURRENT_TIMESTAMP), '
            .'(?, CURRENT_TIMESTAMP), (?, CURRENT_TIMESTAMP), '
            .'(?, CURRENT_TIMESTAMP)',
            ['Homer', 'Marge', 'Lisa', 'Bart', 'Meggy', 'Santa\'\'s Helper', 'Cat']
        );
        $this->assertEquals('', $connection->getError());
        $this->assertEquals(7, $result);
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
