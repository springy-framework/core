<?php

/**
 * Interface of operators for comparation conditions.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Query;

/**
 * Interface of operators for comparation conditions.
 */
interface OperatorComparationInterface
{
    public const OP_EQUAL = '=';
    public const OP_NOT_EQUAL = '!=';
    public const OP_GREATER = '>';
    public const OP_GREATER_EQUAL = '>=';
    public const OP_LESS = '<';
    public const OP_LESS_EQUAL = '<=';
    public const OP_IN = 'IN';
    public const OP_NOT_IN = 'NOT IN';
    public const OP_IS = 'IS';
    public const OP_IS_NOT = 'IS NOT';
    public const OP_LIKE = 'LIKE';
    public const OP_NOT_LIKE = 'NOT LIKE';
    public const OP_MATCH = 'MATCH';
    public const OP_MATCH_BOOLEAN_MODE = 'MATCH BOOLEAN';
}
