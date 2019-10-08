<?php

/**
 * Validation rule for checks for data against a regular expression.
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
 * Validation rule for checks for data against a regular expression.
 */
class ValidateRegex extends ValidationRule
{
    /**
     * Validates the given value.
     *
     * Validate value using regular expression.
     *
     * @return bool
     */
    protected function isValueValid(): bool
    {
        return preg_match(implode(',', $this->params), $this->value);
    }
}
