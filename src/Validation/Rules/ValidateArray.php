<?php

/**
 * Validation rule for array data.
 *
 * @copyright 2019 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0
 */

namespace Springy\Validation\Rules;

class ValidateArray extends ValidationRule
{
    /**
     * Validates the given value.
     *
     * Validates whether the value is an array.
     *
     * @return bool
     */
    protected function isValueValid(): bool
    {
        return is_array($this->value);
    }
}
