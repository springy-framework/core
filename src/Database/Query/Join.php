<?php
/**
 * SQL JOIN clause constructor class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Query;

use Springy\Exceptions\SpringyException;

class Join extends CommandBase implements OperatorComparationInterface, OperatorGroupInterface
{
    const INNER = 'INNER JOIN';
    const LEFT_OUTER = 'LEFT OUTER JOIN';
    const RIGHT_OUTER = 'RIGHT OUTER JOIN';
    const OUTER = 'OUTER JOIN';

    protected $joinType;

    public function __construct(string $table, string $join = self::INNER, Conditions $onCondition = null)
    {

        $this->table = $table;
        $this->joinType = $join;
        $this->parameters = [];

        parent::__construct($onCondition);
    }

    public function __toString()
    {
        $join = $this->joinType.' '.$this->getTableNameAndAlias().$this->getOn();

        return $join;
    }

    protected function getOn()
    {
        if (!$this->conditions->count()) {
            throw new SpringyException('Join condition ON undefined');
        }

        return ' ON '.$this->conditions;
    }

    public function addOnColumns(
        string $column1,
        string $column2,
        string $operator = self::OP_EQUAL,
        string $expression = self::COND_AND
    ) {
        $this->conditions->addColumnsComparation($column1, $column2, $operator, $expression);
    }

    /**
     * Adds a condition to the conditions list.
     *
     * @param string $column      the column name.
     * @param mixed  $value       the value of the condition.
     * @param string $operator    the comparison operator.
     * @param string $expression  the expression to put before this condition.
     * @param bool   $compareCols set this true to define the value as a column name or a function
     *
     * @return void
     */
    public function addOnCondition(
        string $column,
        $value = null,
        string $operator = self::OP_EQUAL,
        string $expression = self::COND_AND,
        bool $compareCols = false
    ) {
        $this->conditions->add($column, $value, $operator, $expression, $compareCols);
    }
}
