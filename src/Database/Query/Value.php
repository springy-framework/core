<?php

/**
 * Object for values data.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Query;

/**
 * Object for values data.
 */
class Value
{
    /** @var string the column name */
    protected $column;
    /** @var bool determines whether the value is a sql expression */
    protected $isExpression;
    /** @var mixed the comparation value */
    protected $value;

    /**
     * Constructor.
     *
     * @param string $column
     * @param mixed  $value
     * @param bool   $isExpression set this true to define the value as SQL expression
     */
    public function __construct(
        string $column,
        $value,
        bool $isExpression = false
    ) {
        $this->column = $column;
        $this->value = $value;
        $this->isExpression = $isExpression;
    }

    /**
     * Gets the column name.
     *
     * @return string
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * Gets the value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Informs if this value is an expression.
     *
     * @return bool
     */
    public function isExpression(): bool
    {
        return $this->isExpression;
    }
}
