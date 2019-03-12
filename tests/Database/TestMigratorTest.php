<?php
/**
 * Test case for Springy\Database\Migrator class.
 *
 * ... and its sub-classes.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Database\Migration\Migrator;
use Springy\Database\Migration\Revisions;

class TestMigratorTest extends TestCase
{
    public function testRevisions()
    {
        $path = config_get('database.connections.mysql.migration_dir');

        $this->assertTrue(is_dir($path));

        $revisions = new Revisions($path);
        $this->assertCount(4, $revisions->getRevisions());
    }

    public function testMigrator()
    {
        $migrator = new Migrator('mysql');

        $this->assertEquals(0, $migrator->getAppliedRevisionsCount());
        $this->assertEquals(4, $migrator->getNotAppliedRevisions());

        $this->assertEquals(3, $migrator->migrate('1'));
        $this->assertEquals(3, $migrator->getAppliedRevisionsCount());
        $this->assertEquals(1, $migrator->getNotAppliedRevisions());

        $this->assertEquals(1, $migrator->migrate());
        $this->assertEquals(0, $migrator->migrate());
    }

    public function testRollback()
    {
        $migrator = new Migrator('mysql');

        $this->assertEquals(4, $migrator->getAppliedRevisionsCount());
        $this->assertEquals(0, $migrator->getNotAppliedRevisions());

        $this->assertEquals(1, $migrator->rollback('2'));
        $this->assertEquals(3, $migrator->getAppliedRevisionsCount());
        $this->assertEquals(1, $migrator->getNotAppliedRevisions());

        $this->assertEquals(3, $migrator->rollback());
        $this->assertEquals(0, $migrator->rollback());

        $this->assertEquals(2, $migrator->migrate('0'));
    }
}
