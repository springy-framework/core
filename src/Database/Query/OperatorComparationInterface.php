<?php
/**
 * Interface of operators for comparation conditions.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Query;

interface OperatorComparationInterface
{
    const OP_EQUAL = '=';
    const OP_NOT_EQUAL = '!=';
    const OP_GREATER = '>';
    const OP_GREATER_EQUAL = '>=';
    const OP_LESS = '<';
    const OP_LESS_EQUAL = '<=';
    const OP_IN = 'IN';
    const OP_NOT_IN = 'NOT IN';
    const OP_IS = 'IS';
    const OP_IS_NOT = 'IS NOT';
    const OP_LIKE = 'LIKE';
    const OP_NOT_LIKE = 'NOT LIKE';
    const OP_MATCH = 'MATCH';
    const OP_MATCH_BOOLEAN_MODE = 'MATCH BOOLEAN';
}
