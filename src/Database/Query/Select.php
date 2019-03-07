<?php
/**
 * SQL SELECT command constructor class.
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

class Select extends CommandBase implements OperatorComparationInterface, OperatorGroupInterface
{
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    /** @var Connection the connection object */
    protected $connection;
    /** @var array the GROUP BY columns list */
    protected $groupBy;
    /** @var array the HAVING conditions statement */
    protected $having;
    /** @var array the array of Join objects */
    protected $joins;
    /** @var int the OFFSET value */
    protected $offset;
    /** @var array the array of ORDER BY columns */
    protected $orderBy;
    /** @var int the LIMIT value */
    protected $limit;
    /** @var array the array of parameters filled after cast to string */
    protected $parameters;
    /** @var array the list of rows filled by run() method */
    protected $rows;

    /**
     * Constructor.
     *
     * @param Connection $connection
     * @param string     $table
     * @param string     $alias
     */
    public function __construct(Connection $connection, string $table = null, string $alias = null)
    {
        $this->conditions = new Where();
        $this->connection = $connection;
        $this->having = new Conditions();
        $this->groupBy = [];
        $this->joins = [];
        $this->offset = 0;
        $this->orderBy = [];
        $this->parameters = [];
        $this->table = $table;
        $this->tableAlias = $alias;
        $this->rows = [];
    }

    /**
     * Converts the object to its string format.
     *
     * @return string
     */
    public function __toString()
    {
        $this->parameters = [];

        $select = 'SELECT '.$this->strColumns()
            .' FROM '.$this->getTableNameAndAlias()
            .$this->strJoins()
            .$this->strWhere()
            .$this->strOrderBy()
            .$this->strGroupBy()
            .$this->strHaving()
            .($this->limit ? ' LIMIT '.$this->limit : '')
            .($this->offset ? ' OFFSET '.$this->offset : '');

        return $select;
    }

    /**
     * Returns the GROUP BY clause string.
     *
     * @return string
     */
    protected function strGroupBy(): string
    {
        if (!count($this->groupBy)) {
            return '';
        }

        return ' GROUP BY '.implode(', ', $this->groupBy);
    }

    /**
     * Returns the HAVING clase string.
     *
     * @return string
     */
    protected function strHaving(): string
    {
        $having = $this->having->parse();
        $this->parameters = array_merge($this->parameters, $this->having->params());

        return $having ? ' HAVING '.$this->having : '';
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
            $joins .= ' '.$join;

            $this->parameters = array_merge($this->parameters, $join->params());
        }

        return $joins;
    }

    /**
     * Returns the ORDER BY clase string.
     *
     * @return string
     */
    protected function strOrderBy(): string
    {
        if (!count($this->orderBy)) {
            return '';
        }

        return ' ORDER BY '.implode(', ', $this->orderBy);
    }

    /**
     * Returns the WHERE clause string.
     *
     * @return string
     */
    protected function strWhere(): string
    {
        $where = $this->conditions->parse();
        $this->parameters = $this->conditions->params();

        return $where;
    }

    /**
     * Tests if limit is a positive value.
     *
     * @param int $limit
     *
     * @throws SpringyException
     *
     * @return int
     */
    protected function testLimit(int $limit): int
    {
        if ($limit < 0) {
            throw new SpringyException('Negative limit value.');
        }

        return $limit;
    }

    /**
     * Tests if offset is a positive value.
     *
     * @param int $offset
     *
     * @throws SpringyException
     *
     * @return int
     */
    protected function testOffset(int $offset): int
    {
        if ($offset < 0) {
            throw new SpringyException('Negative offset value.');
        }

        return $offset;
    }

    /**
     * Adds a GROUP BY statement.
     *
     * @param string $statement
     *
     * @return void
     */
    public function addGroupBy(string $statement)
    {
        if (in_array($statement, $this->groupBy)) {
            return;
        }

        $this->groupBy[] = $statement;
    }

    /**
     * Adds a HAVING clause.
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
     * Adds a Join object to the join list.
     *
     * @param Join $join
     *
     * @return void
     */
    public function addJoin(Join $join)
    {
        foreach ($join->getColumns() as $col) {
            $this->addCol($col);
        }

        $this->joins[] = $join;
    }

    /**
     * Adds a ORDER BY sequence.
     *
     * @param string $name
     * @param string $direction
     *
     * @return void
     */
    public function addOrderBy(string $name, string $direction = self::ORDER_ASC)
    {
        $this->orderBy[] = $name.($direction != self::ORDER_ASC ? ' '.$direction : '');
    }

    /**
     * Gets the found rows quantity.
     *
     * @return int
     */
    public function foundRows(): int
    {
        $select = $this->parseFoundRows();

        if ($select === '' && count($this->rows) > 0 && in_array('found_rows', $this->rows[0])) {
            return (int) $this->rows[0]['found_rows'];
        }

        return (int) $this->connection->select($select, $this->parameters)[0]['found_rows'];
    }

    /**
     * Parses the found row counter command.
     *
     * @return string
     */
    public function parseFoundRows(): string
    {
        $this->parameters = [];

        $select = 'SELECT '.$this->strColumns()
        .' FROM '.$this->getTableNameAndAlias()
        .$this->strJoins()
        .$this->strWhere();

        return $this->connection->getConnector()->foundRowsSelect($select);
    }

    /**
     * Parses the SELECT to its optimized paginated syntax.
     *
     * @return string
     */
    public function parsePaginated(): string
    {
        return $this->connection->getConnector()->paginatedSelect($this->__toString());
    }

    /**
     * Runs the SELECT command and returns the found rows.
     *
     * @param bool $paginated
     *
     * @return array
     */
    public function run(bool $paginated = false): array
    {
        $select = ($paginated ? $this->parsePaginated() : $this->__toString());

        $this->rows = $this->connection->select($select, $this->parameters);

        return $this->rows;
    }

    /**
     * Sets the GROUP BY list.
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
     * Sets the limit or rows.
     *
     * @param int $limit
     *
     * @return void
     */
    public function setLimit(int $limit = null)
    {
        if ($limit === null) {
            $this->limit = $limit;
        }

        $this->limit = $this->testLimit($limit);
    }

    /**
     * Sets the offset row.
     *
     * @param int $offset
     *
     * @return void
     */
    public function setOffset(int $offset)
    {
        $this->offset = $this->testOffset($offset);
    }

    /**
     * Sets the ORDER BY sequence list.
     *
     * @param array $orderBy
     *
     * @return void
     */
    public function setOrderBy(array $orderBy)
    {
        $this->orderBy = [];

        foreach ($orderBy as $statement => $direction) {
            $this->addOrderBy($statement, $direction);
        }
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
