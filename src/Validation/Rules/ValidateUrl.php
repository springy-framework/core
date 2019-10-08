<?php

/**
 * Validation rule for checks for URL data.
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
 * Validation rule for checks for URL data.
 */
class ValidateUrl extends ValidationRule
{
    /**
     * Validates the given value.
     *
     * Validates if the value is a URL.
     *
     * @return bool
     */
    protected function isValueValid(): bool
    {
        return filter_var($this->value, FILTER_VALIDATE_URL) !== false;
    }
}
