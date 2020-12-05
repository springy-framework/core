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
class Update extends CommandBase implements OperatorComparationInterface, OperatorGroupInterface
{
    /** @var Connection the connection object */
    protected $connection;
    /** @var array the array of Join objects */
    protected $joins;
    /** @var array the array of parameters filled after cast to string */
    protected $parameters;
    /** @var bool throws error if there is no condition */
    protected $safeMode;
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
        $this->conditions = new Where();
        $this->connection = $connection;
        $this->joins = [];
        $this->parameters = [];
        $this->safeMode = true;
        $this->table = $table;
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

        $update = 'UPDATE ' . $this->getTableNameAndAlias()
            . $this->strJoins()
            . $this->strSet()
            . $this->strWhere();

        return $update;
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
        $joins = '';
        foreach ($this->joins as $join) {
            $joins .= ' ' . $join;

            $this->parameters = array_merge($this->parameters, $join->params());
        }

        return $joins;
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

        $values = ' SET ';
        foreach ($this->values as $value) {
            $values .= $value->getColumn() . ' = ';

            if ($value->isExpression()) {
                $values .= $value->getValue() . ',';

                continue;
            }

            $values .= '?, ';
            $this->parameters[] = $value->getValue();
        }

        return rtrim($values, ', ');
    }

    /**
     * Returns the WHERE clause string.
     *
     * @return string
     */
    protected function strWhere(): string
    {
        if ($this->safeMode && !$this->conditions->count()) {
            throw new SpringyException('UPDATE without conditions is dangerous.');
        }

        $where = $this->conditions->parse();
        $this->parameters = array_merge($this->parameters, $this->conditions->params());

        return $where;
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
    public function addCondition(
        string $column,
        $value = null,
        string $operator = self::OP_EQUAL,
        string $expression = self::COND_AND,
        bool $compareCols = false
    ) {
        $this->conditions->add($column, $value, $operator, $expression, $compareCols);
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

    /**
     * Runs the UPDATE command and returns the quantity of affected rows.
     *
     * @return int
     */
    public function run(): int
    {
        return $this->connection->update($this->__toString(), $this->parameters);
    }

    /**
     * Turns the safe update mode on|off.
     *
     * @param bool $safe
     *
     * @return void
     */
    public function setSafeMode(bool $safe)
    {
        $this->safeMode = $safe;
    }

    /**
     * Sets teh where condition.
     *
     * @param Where $where
     *
     * @return void
     */
    public function setWhere(Where $where)
    {
        $this->conditions = $where;
    }
}
