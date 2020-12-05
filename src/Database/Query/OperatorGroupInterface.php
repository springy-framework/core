<?php

/**
 * Interface of operators for set of conditions.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Query;

/**
 * Interface of operators for set of conditions.
 */
interface OperatorGroupInterface
{
    public const COND_AND = 'AND';
    public const COND_OR = 'OR';
}
