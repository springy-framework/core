<?php

/**
 * Validation rule for checks integer data.
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
 * Validation rule for checks integer data.
 */
class ValidateInteger extends ValidationRule
{
    /**
     * Validates the given value.
     *
     * Validates whether the value is an integer.
     *
     * @return bool
     */
    protected function isValueValid(): bool
    {
        return filter_var($this->value, FILTER_VALIDATE_INT) !== false;
    }
}
