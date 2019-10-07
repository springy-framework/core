<?php

/**
 * Validation rule for alphabetical data.
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
 * Validation rule for alphabetical data.
 */
class ValidateAlpha extends ValidationRule
{
    /**
     * Validates the given value.
     *
     * Validates whether the value has only letters.
     *
     * @return bool
     */
    protected function isValueValid(): bool
    {
        return preg_match('/^\pL+$/u', $this->value);
    }
}