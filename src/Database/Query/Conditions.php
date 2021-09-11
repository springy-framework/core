<?php

/**
 * Database conditions clauses constructor class.
 *
 * @copyright 2016 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Query;

/**
 * Database conditions clauses constructor class.
 */
class Conditions implements OperatorComparationInterface, OperatorGroupInterface
{
    /** @var array the conditions */
    protected $conditions;
    /** @var array of ? parameters for prepare */
    protected $parameters;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->clear();
    }

    /**
     * Adds the condition value to the parameters array.
     *
     * @param Condition $condition
     *
     * @return void
     */
    protected function addParameters(Condition $condition)
    {
        $value = $condition->value;

        if (is_array($value)) {
            $this->parameters = array_merge($this->parameters, $value);

            return;
        }

        $this->parameters[] = $value;
    }

    /**
     * Converts the objet to a string in database conditions form.
     *
     * The values of the parameter will be in question mark form and can be obtained with params() method.
     *
     * @return string
     */
    public function __toString()
    {
        $this->parameters = [];
        $result = '';

        foreach ($this->conditions as $condition) {
            $condStr = '';

            if ($condition instanceof Condition) {
                $condStr = (
                    empty($result)
                    ? ''
                    : $condition->expression . ' '
                ) . $condition;

                $this->addParameters($condition);
            } elseif (is_array($condition) && $condition[0] instanceof self) {
                $condStr = (
                    empty($result)
                    ? ''
                    : $condition[1] . ' '
                ) . '(' . $condition[0]->parse() . ')';

                $this->parameters = array_merge($this->parameters, $condition[0]->params());
            }

            $result .= ' ' . $condStr;
        }

        return trim($result);
    }

    /**
     * Finds a column in the array of conditions.
     *
     * @param string $column     the name of the column.
     * @param array  $conditions the array of the conditions.
     *
     * @return mixed the condition for given column or false if not found.
     */
    protected function find(string $column, array $conditions)
    {
        foreach ($conditions as $condition) {
            if (is_array($condition) && $condition[0] instanceof self) {
                return $condition[0]->find($column);
            } elseif ($condition instanceof Condition && $condition->column == $column) {
                return $condition;
            }
        }

        return false;
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
    public function add(
        string $column,
        $value = null,
        string $operator = self::OP_EQUAL,
        string $expression = self::COND_AND,
        bool $compareCols = false
    ) {
        $this->conditions[] = new Condition($column, $value, $operator, $expression, $compareCols);
    }

    /**
     * Adds a condition at the conditions list to compares two columns.
     *
     * @param string $column1    the first column name.
     * @param mixed  $column2    the second column name.
     * @param string $operator   the comparison operator.
     * @param string $expression the expression to put before this condition.
     *
     * @return void
     */
    public function addColumnsComparation(
        string $column1,
        string $column2,
        string $operator = self::OP_EQUAL,
        string $expression = self::COND_AND
    ) {
        $this->conditions[] = new Condition($column1, $column2, $operator, $expression, true);
    }

    /**
     * Ads a set of subconditions to the condition list.
     *
     * @param self   $conditions
     * @param string $expression
     *
     * @return void
     */
    public function addSubConditions(self $conditions, $expression = self::COND_AND)
    {
        $this->conditions[] = [$conditions, $expression];
    }

    /**
     * Clears the clause.
     *
     * @return void
     */
    public function clear()
    {
        $this->conditions = [];
        $this->parameters = [];
    }

    /**
     * Returns the number of conditions.
     *
     * @return int
     */
    public function count()
    {
        return count($this->conditions);
    }

    /**
     * Gets the content of conditions in internal array form.
     *
     * @param mixed $column the name of the column or null to all conditions.
     *
     * @return mixed An array of conditions or false.
     */
    public function get(string $column = null)
    {
        if (is_null($column)) {
            return $this->conditions;
        }

        return $this->find($column, $this->conditions);
    }

    /**
     * Gets the params after parse the clause.
     *
     * @return array of parameters in same sequence of the question marks into conditional string.
     */
    public function params(): array
    {
        return $this->parameters;
    }

    /**
     * An alias to __toString() method.
     *
     * @return string
     */
    public function parse(): string
    {
        return $this->__toString();
    }

    /**
     * Removes all ocurrences of the column in the conditions.
     *
     * @param string $column
     *
     * @return void
     */
    public function remove(string $column)
    {
        foreach ($this->conditions as $key => $condition) {
            if ($condition instanceof Condition && $condition->column == $column) {
                unset($this->conditions[$key]);
            } elseif (is_array($condition) && $condition[0] instanceof self) {
                $condition[0]->remove($column);
            }
        }
    }
}
