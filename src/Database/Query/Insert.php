<?php
/**
 * SQL INSERT class constructor.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Query;

use Springy\Database\Connection;
use Springy\Exceptions\SpringyException;

class Insert extends CommandBase implements OperatorComparationInterface, OperatorGroupInterface
{
    const MYSQL_HIGH_PRIORITY = 'HIGH_PRIORITY';
    const MYSQL_LOW_PRIORITY = 'LOW_PRIORITY';

    /** @var Connection the connection object */
    protected $connection;
    /** @var bool turns on/off error be ignored */
    protected $ignoreError;
    /** @var array the array of parameters filled after cast to string */
    protected $parameters;
    /** @var string priority modifier */
    protected $priority;
    /** @var array the array if values to insert */
    protected $values;

    public function __construct(Connection $connection, string $table = null)
    {
        $this->connection = $connection;
        $this->ignoreError = false;
        $this->parameters = [];
        $this->priority = '';
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

        $insert = $this->fetchIgnoreError(
            'INSERT '
            .($this->priority ? $this->priority.' ' : '')
            .'INTO '.$this->getTableName()
            .'('.$this->strColumns().') VALUES '
            .$this->strValues()
        );

        return $insert;
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
        if (!in_array($value->getColumn(), $this->columns)) {
            $this->addCol($value->getColumn());
        }

        if (current($this->values) === false) {
            $this->values[] = [];
            end($this->values);
        }

        $key = key($this->values);

        if (isset($this->values[$key][$value->getColumn()])) {
            throw new SpringyException('Value "'.$value->getColumn().'" redeclared.');
        }

        $this->values[$key][$value->getColumn()] = $value;
    }

    /**
     * Gets the connector class name without namespace.
     *
     * @return string
     */
    protected function getConnectorClass(): string
    {
        return str_replace(
            'Springy\\Database\\Connectors\\', '',
            get_class($this->connection->getConnector())
        );
    }

    /**
     * Parses INSERT string with ignore error.
     *
     * @param string $select
     *
     * @return string
     */
    protected function fetchIgnoreError(string $select): string
    {
        if (!$this->ignoreError) {
            return $select;
        }

        $connector = $this->getConnectorClass();

        $regex = [
            'MySQL'      => '^(INSERT ((HIGH|LOW)_PRIORITY )?)(.+)$',
            'PostgreSQL' => '^(.+)$',
            'SQLite'     => '^(INSERT ((HIGH|LOW)_PRIORITY )?)(.+)$',
        ];
        $replace = [
            'MySQL'      => '$1IGNORE $4',
            'PostgreSQL' => '$1 ON CONFLICT DO NOTHING',
            'SQLite'     => '$1OR IGNORE $4',
        ];

        return preg_replace('/'.$regex[$connector].'/', $replace[$connector], $select);
    }

    /**
     * Returns the values string.
     *
     * @param array $values
     *
     * @return string
     */
    protected function strColValues(array $values): string
    {
        if (!count($values)) {
            throw new SpringyException('Empty column values set.');
        } elseif (count($this->columns) !== count($values)) {
            throw new SpringyException('Columns and values quantity does not mismatch.');
        }

        $strValues = '(';

        foreach ($this->columns as $column) {
            if (!isset($values[$column])) {
                throw new SpringyException('Column "'.$column.'" has no correspondent value.');
            }

            $value = $values[$column];

            if ($value->isExpression()) {
                $strValues .= $value->getValue().',';

                continue;
            }

            $strValues .= '?, ';
            $this->parameters[] = $value->getValue();
        }

        return rtrim($strValues, ', ').')';
    }

    /**
     * Returns the VALUES clause string.
     *
     * @throws SpringyException
     *
     * @return string
     */
    protected function strValues(): string
    {
        if (!count($this->values)) {
            throw new SpringyException('Empty values set.');
        }

        $multiValues = [];
        foreach ($this->values as $values) {
            $multiValues[] = $this->strColValues($values);
        }

        return implode(', ', $multiValues);
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
     * Adds a list of values.
     *
     * @param array $values
     *
     * @return void
     */
    public function addValues(array $values)
    {
        $this->values[] = [];
        end($this->values);

        foreach ($values as $value) {
            if (!($value instanceof Value)) {
                throw new SpringyException('The values must be instances of Springy\Database\Query\Value class.');
            }

            $this->addValueObj($value);
        }
    }

    /**
     * Turns the ignore error on/off.
     *
     * @param bool $ignore
     *
     * @return void
     */
    public function setIgnoreError(bool $ignore)
    {
        $this->ignoreError = $ignore;
    }

    /**
     * Sets priority modifier.
     *
     * @param string $priority
     *
     * @return void
     */
    public function setPriority(string $priority)
    {
        $priorities = [
            'MySQL' => [
                self::MYSQL_HIGH_PRIORITY,
                self::MYSQL_LOW_PRIORITY,
            ],
        ];

        $connector = $this->getConnectorClass();

        if (!isset($priorities[$connector])) {
            throw new SpringyException('Connector "'.$connector.'" does not support priority.');
        } elseif (!in_array(strtoupper($priority), $priorities[$connector])) {
            throw new SpringyException('Unknown priority modifier.');
        }

        $this->priority = strtoupper($priority);
    }
}
