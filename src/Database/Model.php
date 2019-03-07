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
use Springy\Database\Query\Conditions;
use Springy\Database\Query\Delete;
use Springy\Database\Query\Select;
use Springy\Database\Query\Update;
use Springy\Database\Query\Where;
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
     * The name of column used as soft delete control.
     *
     * If undefined the model will catch them from columns list
     * searching for column where property 'sd' => true.
     *
     * @var string
     */
    protected $deletedColumn;

    /**
     * The primary key columns list.
     *
     * If empty the model will catch them from columns list
     * searching for columns where property 'pk' => true.
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

    /** @var bool turns triggers execution off */
    protected $bypassTriggers;
    /** @var array the current row key */
    protected $currentKey;
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
        $this->bypassTriggers = false;
        $this->dbIdentity = $dbIdentity ?? $this->dbIdentity;
        $this->foundRows = 0;
        $this->groupBy = [];
        $this->having = new Conditions();
        $this->joins = [];
        $this->loaded = false;
        $this->rows = [];

        $this->detectPK();
        $this->detectSD();

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

    /**
     * Builds the SQL object to delete rows with given condition.
     *
     * @param Where $where
     *
     * @return Delete|Update
     */
    protected function buildDelete(Where $where)
    {
        if ($this->deletedColumn) {
            $delete = new Update(new Connection($this->dbIdentity), $this->table);
            $delete->addValue($this->deletedColumn, 1);
            $delete->setWhere($where);

            return $delete;
        }

        $delete = new Delete(new Connection($this->dbIdentity), $this->table);
        $delete->setWhere($where);

        return $delete;
    }

    /**
     * Builds the condition by given data.
     *
     * @param Where|int|string|array|null $where
     *
     * @throws SpringyException
     *
     * @return Where
     */
    protected function buildWhere($where = null): Where
    {
        $this->currentKey = [];

        if ($where instanceof Where) {
            return $where;
        } elseif ((is_int($where) || is_string($where)) && count($this->primaryKey) === 1) {
            $this->currentKey[] = $where;
            $where = new Where();
            $where->add($this->primaryKey[0], $this->currentKey[0]);
        } elseif (is_array($where) && count($this->primaryKey) === count($where)) {
            $this->currentKey = $where;
            $where = new Where();
            foreach ($this->primaryKey as $key => $value) {
                $where->add($value, $this->currentKey[$key]);
            }
        }

        if ($where instanceof Where) {
            return $where;
        }

        throw new SpringyException('Invalid condition.');
    }

    /**
     * Builds a Where object from primary key of the current row.
     *
     * @return Where
     */
    protected function buildWhereFromRow(): Where
    {
        if (!$this->hasPK()) {
            throw new SpringyException('Current row has no primary key.');
        }

        $row = current($this->rows);
        $where = new Where();

        foreach ($this->primaryKey as $column) {
            $where->add($column, $row[$column]);
        }

        return $where;
    }

    protected function checkTrigger(string $triggerName): bool
    {
        if ($this->bypassTriggers || !is_callable([$this, $triggerName])) {
            return true;
        }

        return call_user_func([$this, $triggerName]);
    }

    /**
     * Detects the primary key columns if not defined.
     *
     * @return void
     */
    protected function detectPK()
    {
        if (count($this->primaryKey)) {
            return;
        }

        foreach ($this->columns as $name => $properties) {
            if ($properties['pk'] ?? false) {
                $this->primaryKey[] = $name;
            }
        }
    }

    /**
     * Detects the soft deleted column if not defined.
     *
     * @return void
     */
    protected function detectSD()
    {
        if (is_string($this->deletedColumn)) {
            return;
        }

        foreach ($this->columns as $name => $properties) {
            if ($properties['sd'] ?? false) {
                $this->deletedColumn = $name;

                return;
            }
        }
    }

    /**
     * Fetches the current row as array or object.
     *
     * @param array|bool $row
     *
     * @return array|object|bool
     */
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

    /**
     * Checks whether the primary key is set in the current row.
     *
     * @return bool
     */
    protected function hasPK(): bool
    {
        if (!$this->valid() || !count($this->primaryKey)) {
            return false;
        }

        $row = current($this->rows);

        foreach ($this->primaryKey as $column) {
            if (!isset($row[$column])) {
                return false;
            }
        }

        return true;
    }

    protected function hasTriggers(array $triggers)
    {
        foreach ($triggers as $trigger) {
            if (!is_callable([$this, $trigger])) {
                return false;
            }
        }

        return true;
    }

    protected function isCurrent(bool $current): bool
    {
        if ($current) {
            return true;
        }

        if (!count($this->currentKey) || !$this->valid() || !$this->hasPK()) {
            return false;
        }

        $row = current($this->rows);

        foreach ($this->primaryKey as $index => $column) {
            if ($row[$column] != $this->currentKey[$index]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Adds a statement to the group by clause.
     *
     * @param string $statement
     *
     * @return void
     */
    public function addGroupBy(string $statement)
    {
        $this->groupBy[] = $statement;
    }

    /**
     * Adds a statement to the having clause.
     *
     * @param string $statement
     * @param mixed  $value
     * @param string $operator
     * @param string $expression
     *
     * @return void
     */
    public function addHaving(
        string $statement,
        $value = null,
        string $operator = self::OP_EQUAL,
        string $expression = self::COND_AND
    ) {
        $this->having->add($statement, $value, $operator, $expression);
    }

    /**
     * Adds a Join object to the list.
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
     * Clears the group by clause.
     *
     * @return void
     */
    public function clearGroupBy()
    {
        $this->groupBy = [];
    }

    /**
     * Clears the having clause.
     *
     * @return void
     */
    public function clearHaving()
    {
        $this->having->clear();
    }

    /**
     * Clears the join clause.
     *
     * @return void
     */
    public function clearJoin()
    {
        $this->joins = [];
    }

    public function delete($where = null): int
    {
        $current = false;

        if ($where === null) {
            $where = $this->buildWhereFromRow();
            $current = true;
        }

        $filter = $this->buildWhere($where);
        $current = $this->isCurrent($current);

        $triggers = ['triggerBeforeDelete', 'triggerAfterDelete'];

        if (!$current) {
            // delete others
            return 0;
        }

        $delete = $this->buildDelete($filter);

        if (!$this->checkTrigger($triggers[0])) {
            return 0;
        }

        $result = $delete->run();

        $this->checkTrigger($triggers[1]);

        return $result;
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
     * Returns an array with the primary key columns.
     *
     * @return array
     */
    public function getPKColumns(): array
    {
        return $this->primaryKey;
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
        $filter = $this->buildWhere($where);

        $this->select($filter);
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

        $select = new Select(new Connection($this->dbIdentity), $this->table);
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

    /**
     * Turns the fetch mode as object on|off.
     *
     * @param bool $asObject
     *
     * @return void
     */
    public function setFetchAsObject(bool $asObject)
    {
        $this->fetchAsObject = $asObject;
    }

    /**
     * Sets the group by clause list.
     *
     * @param array $groupBy
     *
     * @return void
     */
    public function setGroupBy(array $groupBy)
    {
        $this->groupBy = [];

        foreach ($groupBy as $col) {
            $this->addGroupBy($col);
        }
    }

    /**
     * Sets the having clause conditions.
     *
     * @param Conditions $having
     *
     * @return void
     */
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
