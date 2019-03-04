<?php
/**
 * Test case for Springy\Database\Conditions class.
 *
 * @copyright 2016 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   2.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Database\Condition;
use Springy\Database\Conditions;

class ConditionsTest extends TestCase
{
    protected $conditions;

    public function setUp()
    {
        $this->conditions = new Conditions();
        $this->conditions->add('column_a', 0);
        $this->conditions->add('column_b', 'Foo');
    }

    public function testCount()
    {
        $this->assertEquals(2, $this->conditions->count());
    }

    public function testClear()
    {
        $this->conditions->clear();
        $this->assertCount(0, $this->conditions->get());
    }

    public function testGet()
    {
        $condition = $this->conditions->get('column_a');
        $this->assertInstanceOf(Condition::class, $condition);
        $this->assertEquals('column_a', $condition->column);

        $condition = $this->conditions->get('column_b');
        $this->assertInstanceOf(Condition::class, $condition);
        $this->assertEquals('column_b', $condition->column);

        $condition = $this->conditions->get('column_c');
        $this->assertFalse($condition);
    }

    public function testParse()
    {
        $string = $this->conditions->parse();
        $this->assertEquals('column_a = ? AND column_b = ?', $string);
    }

    public function testAddValueAsColumn()
    {
        $this->conditions->clear();
        $this->conditions->addColumnsComparation('column_a', 'column_b', Condition::OP_GREATER);

        $string = $this->conditions->parse();
        $this->assertEquals('column_a > column_b', $string);
    }

    public function testToString()
    {
        $string = (string) $this->conditions;
        $this->assertStringStartsWith('column_a = ?', $string);
        $this->assertStringEndsWith('column_b = ?', $string);
        $this->assertEquals('column_a = ? AND column_b = ?', $string);
    }

    public function testParamsAfterParse()
    {
        $this->conditions->parse();
        $this->assertCount(2, $this->conditions->params());
        $this->assertContains('Foo', $this->conditions->params());
        $this->assertEquals([0, 'Foo'], $this->conditions->params());
    }

    public function testSubConditions()
    {
        $conditions = new Conditions();
        $conditions->add('column_c', 3, Condition::OP_GREATER_EQUAL);
        $conditions->add('column_c', 8, Condition::OP_LESS_EQUAL);

        $this->conditions->addSubConditions($conditions);

        $string = (string) $this->conditions;
        $this->assertEquals('column_a = ? AND column_b = ? AND (column_c >= ? AND column_c <= ?)', $string);
        $this->assertEquals([0, 'Foo', 3, 8], $this->conditions->params());
    }
}
