<?php

/**
 * Validation rule for checks for data is not null.
 *
 * @copyright 2019 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0
 */

namespace Springy\Validation\Rules;

/**
 * Validation rule for checks for data is not null.
 */
class ValidateNotnull extends ValidationRule
{
    /**
     * Validates the given value.
     *
     * Validates if the value is not null.
     *
     * @return bool
     */
    protected function isValueValid(): bool
    {
        return $this->value !== null;
    }
}
