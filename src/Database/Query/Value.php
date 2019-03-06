<?php

namespace Springy\Database\Query;

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

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function isExpression(): bool
    {
        return $this->isExpression;
    }
}
