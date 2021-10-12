<?php

/**
 * SQL UPDATE class constructor.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Query;

use Springy\Database\Connection;
use Springy\Exceptions\SpringyException;

/**
 * SQL UPDATE class constructor class.
 */
class Update extends DangerousCommand implements OperatorComparationInterface, OperatorGroupInterface
{
    /** @var string */
    protected $commandName = 'UPDATE';
    /** @var array the array of Join objects */
    protected $joins;
    /** @var array the array if values to update */
    protected $values;

    /**
     * Constructor.
     *
     * @param Connection $connection
     * @param string     $table
     */
    public function __construct(Connection $connection, string $table = null)
    {
        parent::__construct($connection, $table);
        $this->joins = [];
        $this->values = [];
    }

    /**
     * Converts the object to its string form.
     *
     * @return string
     */
    public function __toString()
    {
        $this->parameters = [];

        return 'UPDATE ' . $this->getTableNameAndAlias()
            . $this->strJoins()
            . $this->strSet()
            . $this->strWhere();
    }

    /**
     * Adds a object Value to current values list.
     *
     * @param Value $value
     *
     * @return void
     */
    protected function addValueObj(Value $value)
    {
        if (in_array($value->getColumn(), $this->columns)) {
            throw new SpringyException('Value "' . $value->getColumn() . '" redeclared.');
        }

        $this->addCol($value->getColumn());
        $this->values[] = $value;
    }

    /**
     * Returns the JOIN clauses string.
     *
     * @return string
     */
    protected function strJoins(): string
    {
        $joinStr = '';
        foreach ($this->joins as $join) {
            $joinStr .= ' ' . $join;

            $this->parameters = array_merge($this->parameters, $join->params());
        }

        return $joinStr;
    }

    /**
     * Returns the SET clause string.
     *
     * @throws SpringyException
     *
     * @return string
     */
    protected function strSet(): string
    {
        if (!count($this->values)) {
            throw new SpringyException('Empty values set.');
        }

        $setValues = ' SET ';
        foreach ($this->values as $value) {
            $setValues .= $value->getColumn() . ' = ';

            if ($value->isExpression()) {
                $setValues .= $value->getValue() . ',';

                continue;
            }

            $setValues .= '?, ';
            $this->parameters[] = $value->getValue();
        }

        return rtrim($setValues, ', ');
    }

    /**
     * Adds a Join object to the join list.
     *
     * @param Join $join
     *
     * @return void
     */
    public function addJoin(Join $join)
    {
        $this->joins[] = $join;
    }

    /**
     * Adds a value to the current values list.
     *
     * @param string $column       the column name.
     * @param mixed  $value        the value of the condition.
     * @param bool   $isExpression set this true to define the value as a column name or a function
     *
     * @return void
     */
    public function addValue(
        string $column,
        $value = null,
        bool $isExpression = false
    ) {
        $this->addValueObj(new Value($column, $value, $isExpression));
    }
}
