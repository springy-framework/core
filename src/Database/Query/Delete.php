<?php

/**
 * SQL DELETE command constructor.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Query;

/**
 * SQL DELETE command constructor class.
 */
class Delete extends DangerousCommand implements OperatorComparationInterface, OperatorGroupInterface
{
    /** @var string */
    protected $commandName = 'DELETE';

    /**
     * Converts the object to its string form.
     *
     * @return string
     */
    public function __toString()
    {
        $this->parameters = [];

        return 'DELETE FROM ' . $this->getTableNameAndAlias() . $this->strWhere();
    }
}
