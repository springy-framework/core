<?php
/**
 * Test case for Springy\Database\Where class.
 *
 * @copyright 2016 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 *
 * @version   1.1.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Database\Where;

class WhereTest extends TestCase
{
    protected $where;

    public function setUp()
    {
        $this->where = new Where();
        $this->where->add('column_a', 0);
        $this->where->add('column_b', 'none');
    }

    public function testParse()
    {
        $string = $this->where->parse();
        $this->assertStringStartsWith(' WHERE ', $string);
    }

    public function testToString()
    {
        $string = (string) $this->where;
        $this->assertStringStartsWith(' WHERE ', $string);
    }
}
