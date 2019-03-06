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
use Springy\Database\Where;
use Springy\Database\Join;
use Springy\Exceptions\SpringyException;

class JoinTest extends TestCase
{
    public $join;

    public function setUp()
    {
        $this->join = new Join('test');
    }

    public function testEmptyOn()
    {
        $this->expectException(SpringyException::class);
        $this->join->parse();
    }

    public function testSimpleJoin()
    {
        $this->join->addOnColumns('id', 'foreign_id');

        $sql = 'INNER JOIN test ON id = foreign_id';
        $this->assertEquals($sql, $this->join->parse());
    }

    public function testSelectWithAliasForTableName()
    {
        $this->join->setTable('test', 't');
        $this->join->addOnColumns('id', 'foreign_id');

        $sql = 'INNER JOIN test AS t ON id = foreign_id';
        $this->assertEquals($sql, (string) $this->join);
    }
}