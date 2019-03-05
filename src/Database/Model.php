<?php
/**
 * Parent class for relational database table model objects.
 *
 * @copyright 2014 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @author    Allan Marques <allan.marques@ymail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   3.0.0
 */

namespace Springy\Database;

use Iterator;
use Springy\Exceptions\SpringyException;

class Model implements Iterator
{
    /**
     * The table name.
     *
     * Must be defined in the heir class.
     *
     * @var string
     */
    protected $table;

    /**
     * The columns structure.
     *
     * Must be defined in the heir class.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * The primary key columns list.
     *
     * @var array
     */
    protected $primaryKey = [];

    /**
     * Protects against great load data set.
     *
     * @var bool
     */
    protected $abortOnEmptyFilter = true;

    /**
     * The default database configuration identity.
     *
     * @var string
     */
    protected $dbIdentity;

    /**
     * Default limit rows for selects.
     *
     * @var int
     */
    protected $defaultLimit;

    /**
     * Determines what get method does when desired column does not exists.
     *
     * If true, a exception will be throwed.
     * If false, a null value will be returned.
     *
     * @var bool
     */
    protected $errorIfColNotExists = false;

    /**
     * Determines the return mode of the fetch methods.
     *
     * If true, the rows are returned as object.
     * If false, the rows are returned as array.
     *
     * @var bool
     */
    protected $fetchAsObject = false;

    /** @var int the number of rows found by last select */
    protected $foundRows;
    /** @var array the group by elements */
    protected $groupBy;
    /** @var array the having filter elements */
    protected $having;
    /** @var array the list of joins */
    protected $joins;
    /** @var bool informs whether the record was loaded */
    protected $loaded;
    /** @var array the list of columns to the select query */
    protected $selectColumns = [];
    /** @var array group by columns */
    protected $rows;

    /**
     * Constructor.
     *
     * @param Where $filter
     */
    public function __construct($filter = null, string $dbIdentity = null)
    {
        $this->dbIdentity = $dbIdentity ?? $this->dbIdentity;
        $this->foundRows = 0;
        $this->groupBy = [];
        $this->having = new Conditions();
        $this->joins = [];
        $this->loaded = false;
        $this->rows = [];

        if ($filter === null) {
            return;
        }

        $this->load($filter);
    }

    /**
     * Magic method to get value from columns as if they were properties.
     *
     * This method will use the get() method.
     *
     * @param string $name the name of the column.
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * Magic method to set value in columns as if they were properties..
     *
     * This method will use the set() method.
     *
     * @param string $name  the column name.
     * @param mixed  $value the value.
     *
     * @return void
     */
    public function __set(string $name, $value)
    {
        $this->set($name, $value);
    }

    protected function fetchRow($row)
    {
        if (is_bool($row)) {
            return $row;
        } elseif ($this->fetchAsObject) {
            return (object) $row;
        }

        return $row;
    }

    protected function getSelectColumns(): array
    {
        if (count($this->selectColumns)) {
            return $this->selectColumns;
        }

        $columns = [];
        foreach ($this->columns as $name => $data) {
            if ($data['computed'] ?? false) {
                continue;
            }

            $columns[] = $name;
        }

        return $columns;
    }

    public function addGroupBy(string $col)
    {
        $this->groupBy[] = $col;
    }

    public function addHaving(
        string $statement,
        $value = null,
        string $operator = self::OP_EQUAL,
        string $expression = self::COND_AND
    ) {
        $this->having->add($statement, $value, $operator, $expression);
    }

    public function addJoin(Join $join)
    {
        $this->joins[] = $join;
    }

    public function clearGroupBy()
    {
        $this->groupBy = [];
    }

    public function clearHaving()
    {
        $this->having->clear();
    }

    public function clearJoin()
    {
        $this->joins = [];
    }

    /**
     * Returns the number of records found by the last select.
     *
     * @return int
     */
    public function foundRows(): int
    {
        return $this->foundRows;
    }

    /**
     * Gets a column or a row from the resultset.
     *
     * @param string $column the name of the desired column or null to all columns of the current record.
     *
     * @return mixed
     */
    public function get(string $column = null)
    {
        if ($column === null) {
            return $this->current();
        }

        $columns = current($this->rows);

        if (!isset($columns[$column]) && $this->errorIfColNotExists) {
            throw new SpringyException('Column "'.$column.'" does not exists.');
        }

        return $columns[$column] ?? null;
    }

    /**
     * Returns whether the desired record has been loaded.
     *
     * @return bool
     */
    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    /**
     * Loads a record.
     *
     * @param mixed $where
     *
     * @return bool True if one and only one row was found or false in other case.
     */
    public function load($where)
    {
        if ((is_int($where) || is_string($where)) && count($this->primaryKey) === 1) {
            $key = $where;
            $where = new Where();
            $where->add($this->primaryKey[0], $key);
        } elseif (is_array($where) && count($this->primaryKey) === count($where)) {
            $keys = $where;
            $where = new Where();
            foreach ($this->primaryKey as $key => $value) {
                $where->add($value, $keys[$key]);
            }
        } elseif (!($where instanceof Where)) {
            throw new SpringyException('Invalid load condition.');
        }

        $this->select($where);
        $this->loaded = ($this->foundRows === 1);

        return $this->loaded;
    }

    /**
     * Returns all rows.
     *
     * @return array
     */
    public function rows(): array
    {
        return $this->rows;
    }

    /**
     * Returns the number of rows.
     *
     * @return int
     */
    public function rowsCount(): int
    {
        return count($this->rows);
    }

    /**
     * Performs a select.
     *
     * @param Where $where
     * @param array $orderby a multidimentional array with column => 'ASC|DESC' pairs.
     * @param int   $offset
     * @param int   $limit
     *
     * @return void
     */
    public function select(Where $where = null, array $orderby = null, int $offset = 0, int $limit = null)
    {
        $this->loaded = false;
        $this->rows = [];

        $limit = $limit ?? $this->defaultLimit ?? 0;

        if ($this->abortOnEmptyFilter && !$where->count() && !$limit) {
            return;
        }

        $connection = new Connection($this->dbIdentity);
        $select = new Select($connection, $this->table);
        $select->setWhere($where);
        $select->setLimit($limit);
        $select->setOffset($offset);
        $select->setHaving($this->having);

        foreach ($this->getSelectColumns() as $column) {
            $select->addColumn($column);
        }

        foreach ($this->joins as $join) {
            $select->addJoin($join);
        }

        foreach ($this->groupBy as $col) {
            $select->addGroupBy($col);
        }

        foreach ($orderby ?? [] as $key => $value) {
            $select->addOrderBy($key, $value);
        }

        $this->rows = $select->run(true);
        $this->foundRows = $select->foundRows();

        // $this->_queryEmbbed($embbed);

        // Set the values of the calculated columns
        // $this->calculateColumns();
    }

    public function setFetchAsObject(bool $asObject)
    {
        $this->fetchAsObject = $asObject;
    }

    public function setGroupBy(array $groupBy)
    {
        $this->groupBy = [];

        foreach ($groupBy as $col) {
            $this->addGroupBy($col);
        }
    }

    public function setHaving(Conditions $having)
    {
        $this->having = $having;
    }

    /**
     * Gets the current record.
     *
     * @return array|object|bool
     */
    public function current()
    {
        return $this->fetchRow(current($this->rows));
    }

    /**
     * Moves the pointer to the last record.
     *
     * @return void
     */
    public function end()
    {
        end($this->rows);
    }

    /**
     * Moves the pointer to the next record.
     *
     * @return void
     */
    public function next()
    {
        next($this->rows);
    }

    /**
     * Moves the pointer to the previous record.
     *
     * @return void
     */
    public function prev()
    {
        prev($this->rows);
    }

    /**
     * Rewinds rows cursor to first position.
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->rows);
    }

    /**
     * Gets the names of the columns.
     *
     * @return array|bool
     */
    public function key(): array
    {
        if (!count($this->rows)) {
            return [];
        }

        return array_keys($this->rows[0]);
    }

    /**
     * Checks whether the current record exists.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->current() !== false;
    }
}
