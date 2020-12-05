<?php

/**
 * Validation rule for alpha-numeric data.
 *
 * @copyright 2019 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0
 */

namespace Springy\Validation\Rules;

class ValidateAlphanum extends ValidationRule
{
    /**
     * Validates the given value.
     *
     * Validates whether the value has only letters and numbers.
     *
     * @return bool
     */
    protected function isValueValid(): bool
    {
        return preg_match('/^[\pL\pN]+$/u', $this->value);
    }
}
