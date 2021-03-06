<?php

/**
 * Validation rule for checks for data not is inside a list of values.
 *
 * @copyright 2019 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0
 */

namespace Springy\Validation\Rules;

/**
 * Validation rule for checks for data not is inside a list of values.
 */
class ValidateNotin extends ValidationRule
{
    /**
     * Validates the given value.
     *
     * Validates if the value is not in a list.
     *
     * @return bool
     */
    protected function isValueValid(): bool
    {
        return !in_array($this->value, $this->params);
    }
}
