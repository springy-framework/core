<?php
/**
 * Test case for Springy\Database\Query\Delete class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Database\Connection;
use Springy\Database\Query\Delete;
use Springy\Exceptions\SpringyException;

class DeleteTest extends TestCase
{
    /** @var Delete */
    public $delete;

    protected function setUp(): void
    {
        $connection = new Connection('mysql');
        $this->delete = new Delete($connection, 'test_spf');
    }

    public function testDeleteWithoutWhere()
    {
        $this->expectException(SpringyException::class);
        $this->delete->parse();
    }

    public function testDeleteWithoutSafeMode()
    {
        $this->delete->setSafeMode(false);

        $sql = 'DELETE FROM test_spf';
        $this->assertEquals($sql, (string) $this->delete);
        $this->assertEquals([], $this->delete->params());
    }

    public function testSimpleDelete()
    {
        $this->delete->addCondition('id', 11);

        $sql = 'DELETE FROM test_spf WHERE id = ?';
        $this->assertEquals($sql, $this->delete->parse());
        $this->assertEquals([11], $this->delete->params());
    }

    public function testRun()
    {
        $this->delete->addCondition('id', 1);

        $rows = $this->delete->execute();
        $this->assertEquals(1, $rows);
    }
}
