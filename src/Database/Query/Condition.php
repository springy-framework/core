<?php

/**
 * Database condition clauses constructor.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Query;

use Springy\Exceptions\SpringyException;

/**
 * Database condition clauses constructor class.
 */
class Condition implements OperatorComparationInterface, OperatorGroupInterface
{
    /** @var string the column name */
    protected $column;
    /** @var string the comparation expression */
    protected $expression;
    /** @var string the comparation operator */
    protected $operator;
    /** @var mixed the comparation value */
    protected $value;
    /** @var bool determines whether the value is a column or a funcion */
    protected $valueIsColumn;

    /**
     * Constructor.
     *
     * @param string $column
     * @param mixed  $value
     * @param string $operator
     * @param string $expression
     * @param bool   $compareColumns set this true to define the value as a column name or a function
     */
    public function __construct(
        string $column,
        $value,
        string $operator = self::OP_EQUAL,
        string $expression = self::COND_AND,
        bool $compareColumns = false
    ) {
        $this->column = $column;
        $this->value = $value;
        $this->operator = strtoupper($operator);
        $this->expression = $expression;
        $this->valueIsColumn = $compareColumns;
    }

    /**
     * Gets a property value.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        if (!isset($this->$name)) {
            throw new SpringyException('Property "' . $name . '" does not exists.');
        }

        return $this->$name;
    }

    /**
     * Sets a property value.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set(string $name, $value)
    {
        if (!isset($this->$name)) {
            throw new SpringyException('Property "' . $name . '" does not exists.');
        }

        $this->$name = $value;
    }

    /**
     * Converts the condition object to its string form.
     *
     * @throws SpringyException
     *
     * @return string
     */
    public function __toString(): string
    {
        $conditions = [
            'comparationGeneral' => [
                self::OP_EQUAL,
                self::OP_NOT_EQUAL,
                self::OP_GREATER,
                self::OP_GREATER_EQUAL,
                self::OP_LESS,
                self::OP_LESS_EQUAL,
                self::OP_IS,
                self::OP_IS_NOT,
                self::OP_LIKE,
                self::OP_NOT_LIKE,
            ],
            'comparationIn' => [
                self::OP_IN,
                self::OP_NOT_IN,
            ],
            'comparationMatch' => [
                self::OP_MATCH,
                self::OP_MATCH_BOOLEAN_MODE,
            ],
        ];

        foreach ($conditions as $method => $operators) {
            if (in_array($this->operator, $operators)) {
                return call_user_func([$this, $method]);
            }
        }

        throw new SpringyException('Unknown condition operator.');
    }

    /**
     * Builds a general comparation string.
     *
     * @return string
     */
    protected function comparationGeneral(): string
    {
        return $this->column . ' ' . $this->operator . ' ' . $this->getQuestionMark();
    }

    /**
     * Builds a comparation string for IN and NOT IN condition.
     *
     * @return string
     */
    protected function comparationIn(): string
    {
        return $this->column . (
                $this->operator === self::OP_NOT_IN ? ' NOT' : ''
            ) . ' IN (' . trim(str_repeat('?, ', count($this->value)), ', ') . ')';
    }

    /**
     * Builds a comparation string for MATCH condition.
     *
     * The MATCH condition is used to performs filters for
     * FULLTEXT indexes in MySQL tables.
     *
     * @return string
     */
    protected function comparationMatch(): string
    {
        return 'MATCH (' . $this->column . ') AGAINST (' . $this->getQuestionMark() . (
                $this->operator === self::OP_MATCH_BOOLEAN_MODE ? ' IN BOOLEAN MODE' : ''
            ) . ')';
    }

    /**
     * Gets the question mark or value property as a field name.
     *
     * @return string
     */
    protected function getQuestionMark(): string
    {
        return $this->valueIsColumn ? $this->value : '?';
    }
}
