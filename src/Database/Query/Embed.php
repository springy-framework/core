<?php

/**
 * Embed object.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Query;

/**
 * Embed object class.
 */
class Embed
{
    /** @var string the name for embeded attribute */
    protected $embedName;
    /** @var string the list of keys to find */
    protected $filter;
    /** @var string the name of foreign key in embedded model */
    protected $foreignKey;
    /** @var string the name of referenced column in parent model */
    protected $refColumn;
    /** @var Model the embedded model object */
    protected $model;
    /** @var int the limit of embedded rows */
    protected $limit;
    /** @var int the offset for embedded rows */
    protected $offset;
    /** @var array the order by columns statement */
    protected $orderBy;
    /** @var int the type of embedding */
    protected $type;
    /** @var string the condition to embed */
    protected $when;

    // Embeding type constants
    public const ET_DATA = 1;
    public const ET_LIST = 2;

    /**
     * Constructor.
     *
     * @param string        $embedName
     * @param object|string $model
     * @param string        $foreignKey
     * @param string        $refColumn
     * @param int           $resultType
     */
    public function __construct(
        string $embedName,
        $model,
        string $foreignKey,
        string $refColumn,
        int $resultType = self::ET_DATA
    ) {
        $this->embedName = $embedName;
        $this->filter = [];
        $this->foreignKey = $foreignKey;
        $this->model = is_string($model) ? (new $model()) : $model;
        $this->offset = 0;
        $this->orderBy = [];
        $this->limit = 0;
        $this->refColumn = $refColumn;
        $this->type = $resultType;
    }

    /**
     * Returns the Where object to internal select.
     *
     * @return Where
     */
    protected function getWhere(): Where
    {
        $where = new Where();

        if (count($this->filter) === 1) {
            $where->add($this->foreignKey, $this->filter[0]);

            return $where;
        }

        $where->add($this->foreignKey, $this->filter, Where::OP_IN);

        return $where;
    }

    /**
     * Returns the comparison between values.
     *
     * @param mixed  $left
     * @param mixed  $right
     * @param string $operator
     *
     * @return bool
     */
    protected function matches($left, $right, $operator): bool
    {
        switch ($operator) {
            case '=':
                return $left == $right;
            case '>':
                return $left > $right;
            case '<':
                return $left < $right;
            case '!=':
                return $left != $right;
            case 'in':
            case 'IN':
                return is_array($right) && in_array($left, $right);
        }

        return false;
    }

    /**
     * Returns the name of embedding attribute.
     *
     * @return string
     */
    public function getEmbedName(): string
    {
        return $this->embedName;
    }

    /**
     * Returns the name of column used as foreign key in embedded model.
     *
     * @return string
     */
    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    /**
     * Returns the model object.
     *
     * @return void
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Returns the name of referenced column in parent model.
     *
     * @return string
     */
    public function getRefColumn(): string
    {
        return $this->refColumn;
    }

    /**
     * Returns the result of embeddind.
     *
     * @param array $row
     *
     * @return array|object
     */
    public function getResult(array $row)
    {
        $result = [];

        if (!$this->isEligible($row)) {
            return $result;
        }

        $this->model->rewind();

        while ($this->model->valid()) {
            if ($this->model->get($this->foreignKey) == $row[$this->refColumn]) {
                if ($this->type == self::ET_DATA) {
                    return $this->model->get();
                }

                $result[] = $this->model->get();
            }

            $this->model->next();
        }

        return $result;
    }

    /**
     * When condition for embedded object.
     *
     * @param array $row
     *
     * @return bool
     */
    public function isEligible(array $row): bool
    {
        if (!$this->when) {
            return true;
        }

        $results = [];
        foreach ($this->when as $condition) {
            if (count($condition) < 3) {
                $results[] = false;

                continue;
            }

            $left = $condition[0];
            $right = $condition[2];

            if (is_string($left) && isset($row[$left])) {
                $left = $row[$left];
            }

            if (is_string($right) && isset($row[$right])) {
                $right = $row[$right];
            }

            $results[] = $this->matches($left, $right, $condition[1]);
        }

        $results = array_unique($results);

        return count($results) === 1 ? $results[0] : false;
    }

    /**
     * Does the search in embedded object.
     *
     * @return void
     */
    public function select()
    {
        if (!count($this->filter)) {
            return;
        }

        $where = $this->getWhere();

        $this->model->select($where, $this->orderBy, $this->offset, $this->limit);
    }

    /**
     * Sets the filter values.
     *
     * @param array $value
     *
     * @return void
     */
    public function setFilter(array $value)
    {
        $this->filter = $value;
    }

    /**
     * Sets the limit for embeddin.
     *
     * @param int $limit
     *
     * @return void
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;
    }

    /**
     * Sets the offset of embedding.
     *
     * @param int $offset
     *
     * @return void
     */
    public function setOffset(int $offset)
    {
        $this->offset = $offset;
    }

    /**
     * Sets the order by statement array.
     *
     * @param array $orderBy
     *
     * @return void
     */
    public function setOrderBy(array $orderBy)
    {
        $this->orderBy = $orderBy;
    }

    /**
     * Sets the result type.
     *
     * @param int $type
     *
     * @return void
     */
    public function setResultType(int $type)
    {
        $this->type = $type;
    }
}
